<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\ProductCategory;
use App\Models\ProductPresentationUnit;
use App\Models\MeasurementUnit;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $r)
    {
        $q      = trim((string) $r->get('q', ''));
        $active = $r->get('active', 'all'); // all|1|0

        $products = Product::with(['category', 'presentationUnit', 'concentrationUnit', 'supplier'])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                        ->orWhere('sku', 'like', "%$q%")
                        ->orWhere('barcode', 'like', "%$q%")
                        ->orWhere('brand', 'like', "%$q%");
                });
            })
            ->when($active !== 'all', fn ($qq) => $qq->where('is_active', (int) $active))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // IDs de la página actual
        $pageIds = $products->getCollection()->pluck('id');

        // Stock calculado a partir de movimientos
        $stockMap = InventoryMovement::selectRaw(
            'product_id,
             SUM(
                CASE
                    WHEN type IN ("in","adjust","transfer") THEN qty
                    WHEN type = "out" THEN -qty
                    ELSE 0
                END
             ) AS stock'
        )
            ->whereIn('product_id', $pageIds)
            ->groupBy('product_id')
            ->pluck('stock', 'product_id');

        // Lotes con stock positivo para los productos de la página (para ver vencimiento más próximo)
        $lotsPage = InventoryMovement::selectRaw('product_id, lot, expires_at, SUM(CASE WHEN type="out" THEN -qty ELSE qty END) as stock')
            ->whereIn('product_id', $pageIds)
            ->whereNotNull('expires_at')
            ->groupBy('product_id', 'lot', 'expires_at')
            ->having('stock', '>', 0)
            ->get();

        // Mapa: product_id => fecha vencimiento más próxima
        $nearestExpirationMap = $lotsPage->groupBy('product_id')->map(function ($batches) {
            return $batches->min('expires_at');
        });

        // Contar productos con stock bajo en la página
        $lowStockCountPage = $products->getCollection()->filter(function ($p) use ($stockMap) {
            $stock = (float) ($stockMap[$p->id] ?? 0);
            $min   = (float) $p->min_stock;
            return $min > 0 && $stock <= $min;
        })->count();

        // ESTADÍSTICAS GLOBALES (KPIs) de vencimiento
        // Nota: Esto puede ser pesado si hay muchos movimientos. Optimizamos buscando solo lotes con stock.
        // Si es muy lento, se debería cachear o crear tabla de lotes.
        $globalLots = InventoryMovement::selectRaw('product_id, lot, expires_at, SUM(CASE WHEN type="out" THEN -qty ELSE qty END) as stock')
            ->whereNotNull('expires_at')
            ->groupBy('product_id', 'lot', 'expires_at')
            ->having('stock', '>', 0)
            ->get();
        
        $today = now()->today();
        $expiredCount      = $globalLots->where('expires_at', '<', $today)->count();
        $expiringSoonCount = $globalLots->whereBetween('expires_at', [$today, $today->copy()->addDays(30)])->count();

        return view('admin.inv.products.index', compact(
            'products',
            'q',
            'active',
            'stockMap',
            'lowStockCountPage',
            'expiredCount',
            'expiringSoonCount',
            'nearestExpirationMap'
        ));
    }

    public function create()
    {
        $product = new Product([
            'is_active' => true,
            'min_stock' => 0,
            'unit'      => 'unidad',
        ]);

        $categories        = ProductCategory::orderBy('name')->get();
        $presentationUnits = ProductPresentationUnit::orderBy('name')->get();
        $measurementUnits  = MeasurementUnit::orderBy('name')->get();
        $suppliers         = Supplier::orderBy('name')->get();

        return view('admin.inv.products.create', compact(
            'product',
            'categories',
            'presentationUnits',
            'measurementUnits',
            'suppliers'
        ));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'sku'                   => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')],
            'barcode'               => ['nullable', 'string', 'max:60', Rule::unique('products', 'barcode')],
            'name'                  => ['required', 'string', 'max:120'],
            'product_category_id'   => ['nullable', 'exists:product_categories,id'],
            'presentation_unit_id'  => ['nullable', 'exists:product_presentation_units,id'],
            'presentation_detail'   => ['nullable', 'string', 'max:120'],
            'concentration_value'   => ['nullable', 'numeric', 'min:0'],
            'concentration_unit_id' => ['nullable', 'exists:measurement_units,id'],
            'unit'                  => ['required', 'string', 'max:30'],
            'brand'                 => ['nullable', 'string', 'max:120'],
            'supplier_id'           => ['nullable', 'exists:suppliers,id'],
            'min_stock'             => ['nullable', 'integer', 'min:0'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['min_stock'] = (int) ($data['min_stock'] ?? 0);

        $p = Product::create($data);

        return redirect()
            ->route('admin.inv.products.edit', $p)
            ->with('ok', 'Producto creado');
    }

    public function edit(Product $product)
    {
        $categories        = ProductCategory::orderBy('name')->get();
        $presentationUnits = ProductPresentationUnit::orderBy('name')->get();
        $measurementUnits  = MeasurementUnit::orderBy('name')->get();
        $suppliers         = Supplier::orderBy('name')->get();

        $batches = InventoryMovement::selectRaw('lot, expires_at, SUM(CASE WHEN type="out" THEN -qty ELSE qty END) as stock')
            ->where('product_id', $product->id)
            ->groupBy('lot', 'expires_at')
            ->having('stock', '>', 0)
            ->orderBy('expires_at')
            ->get();

        // Detectar stock "Legacy" (Si no hay movimientos pero el producto tiene stock en columna antigua)
        // Esto pasa con seeders o datos viejos no migrados.
        $totalMovs = $batches->sum('stock');
        if ($totalMovs == 0 && $product->stock > 0) {
            // Creamos un "Lote Virtual" para que el usuario pueda migrarlo
            $virtualBatch = new \stdClass();
            $virtualBatch->lot = 'STOCK_ANTIGUO'; // Marcador especial
            $virtualBatch->expires_at = null;
            $virtualBatch->stock = $product->stock;
            
            // Agregamos a la colección
            $batches->push($virtualBatch);
        }

        return view('admin.inv.products.edit', compact(
            'product',
            'categories',
            'presentationUnits',
            'measurementUnits',
            'suppliers',
            'batches'
        ));
    }

    public function update(Request $r, Product $product)
    {
        $data = $r->validate([
            'sku'                   => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($product->id)],
            'barcode'               => ['nullable', 'string', 'max:60', Rule::unique('products', 'barcode')->ignore($product->id)],
            'name'                  => ['required', 'string', 'max:120'],
            'product_category_id'   => ['nullable', 'exists:product_categories,id'],
            'presentation_unit_id'  => ['nullable', 'exists:product_presentation_units,id'],
            'presentation_detail'   => ['nullable', 'string', 'max:120'],
            'concentration_value'   => ['nullable', 'numeric', 'min:0'],
            'concentration_unit_id' => ['nullable', 'exists:measurement_units,id'],
            'unit'                  => ['required', 'string', 'max:30'],
            'brand'                 => ['nullable', 'string', 'max:120'],
            'supplier_id'           => ['nullable', 'exists:suppliers,id'],
            'min_stock'             => ['nullable', 'integer', 'min:0'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        // Si no viene el checkbox => false
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['min_stock'] = (int) ($data['min_stock'] ?? 0);

        $product->update($data);

        return back()->with('ok', 'Producto actualizado');
    }

    public function destroy(Product $product)
    {
        // No permitir borrar si tiene movimientos
        $hasMovs = InventoryMovement::where('product_id', $product->id)->exists();
        if ($hasMovs) {
            return back()->withErrors('No se puede eliminar: el producto tiene movimientos de inventario. Desactívalo en su lugar.');
        }

        $product->delete();

        return redirect()
            ->route('admin.inv.products.index')
            ->with('ok', 'Producto eliminado');
    }

    public function updateBatch(Request $request, Product $product)
    {
        $data = $request->validate([
            'current_lot' => ['nullable', 'string'],
            'new_lot'     => ['nullable', 'string', 'max:60'],
            'expires_at'  => ['nullable', 'date'],
        ]);

        $currentLot = $data['current_lot'] ?? null;
        $newLot     = $data['new_lot'] ?? $currentLot; 
        $expiresAt  = $data['expires_at'];

        // Caso especial: Migración de STOCK_ANTIGUO
        if ($currentLot === 'STOCK_ANTIGUO') {
             InventoryMovement::create([
                 'product_id' => $product->id,
                 'type'       => 'adjust', // Ajuste inicial
                 'qty'        => $product->stock, // Usamos el stock legacy
                 'lot'        => $newLot,
                 'expires_at' => $expiresAt,
                 'description'=> 'Migración de stock inicial'
             ]);
             
             // Opcional: limpiar stock legacy para no confundir
             // $product->update(['stock' => 0]); 
             
             return back()->with('ok', 'Stock antiguo migrado a lote correctamente.');
        }

        // Caso normal: Actualizar movimientos existentes
        InventoryMovement::where('product_id', $product->id)
            ->where('lot', $currentLot)
            ->update([
                'lot'        => $newLot,
                'expires_at' => $expiresAt
            ]);

        return back()->with('ok', 'Lote actualizado correctamente.');
    }
}
