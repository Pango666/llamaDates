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
        $filter = $r->get('filter'); // expired | soon | low_stock

        // 1. Construir Query Base
        $productsQuery = Product::with(['category', 'presentationUnit', 'concentrationUnit', 'supplier'])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                        ->orWhere('sku', 'like', "%$q%")
                        ->orWhere('barcode', 'like', "%$q%")
                        ->orWhere('brand', 'like', "%$q%");
                });
            })
            ->when($active !== 'all', fn ($qq) => $qq->where('is_active', (int) $active));

        // ESTADÍSTICAS GLOBALES (KPIs) de vencimiento
        // Nota: Esto puede ser pesado si hay muchos movimientos. Optimizamos buscando solo lotes con stock.
        $globalLots = InventoryMovement::selectRaw('product_id, lot, MAX(expires_at) as expires_at, SUM(CASE WHEN type="out" THEN -qty ELSE qty END) as stock')
            ->groupBy('product_id', 'lot')
            ->having('stock', '>', 0)
            ->get();
        
        $today = now()->today();
        $expiredCount      = $globalLots->where('expires_at', '<', $today)->count();
        $expiringSoonCount = $globalLots->whereBetween('expires_at', [$today, $today->copy()->addDays(30)])->count();
        
        // 3. Aplicar Filtros Específicos
        if ($filter === 'expired') {
            $expiredIds = $globalLots->where('expires_at', '<', $today)->pluck('product_id')->unique();
            $productsQuery->whereIn('id', $expiredIds);
        } elseif ($filter === 'soon') {
            $soonIds = $globalLots->whereBetween('expires_at', [$today, $today->copy()->addDays(30)])->pluck('product_id')->unique();
            $productsQuery->whereIn('id', $soonIds);
        } elseif ($filter === 'low_stock') {
            // Subquery para stock actual
            $productsQuery->whereRaw("
                (SELECT COALESCE(SUM(CASE WHEN type IN ('in','adjust','transfer') THEN qty WHEN type = 'out' THEN -qty ELSE 0 END), 0)
                 FROM inventory_movements 
                 WHERE inventory_movements.product_id = products.id) <= products.min_stock
            ")
            ->where('min_stock', '>', 0);
        }

        // 4. Paginar Resultados
        $products = $productsQuery->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // 5. Calcular Datos para la Página Actual
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
        $lotsPage = InventoryMovement::selectRaw('product_id, lot, MAX(expires_at) as expires_at, SUM(CASE WHEN type="out" THEN -qty ELSE qty END) as stock')
            ->whereIn('product_id', $pageIds)
            ->groupBy('product_id', 'lot')
            ->having('stock', '>', 0)
            ->get();

        // Mapa: product_id => fecha vencimiento más próxima
        $nearestExpirationMap = $lotsPage->groupBy('product_id')->map(function ($batches) {
            return $batches->min('expires_at');
        });
        
        // Mapa: product_id => lote del vencimiento más próximo
        $nearestLotMap = $lotsPage->groupBy('product_id')->map(function ($batches) {
            return $batches->sortBy('expires_at')->first()->lot ?? '';
        });

        // Contar productos con stock bajo en la página
        $lowStockCountPage = $products->getCollection()->filter(function ($p) use ($stockMap) {
            $stock = (float) ($stockMap[$p->id] ?? 0);
            $min   = (float) $p->min_stock;
            return $min > 0 && $stock <= $min;
        })->count();

        // Contadores globales de estado
        $activeCount   = Product::where('is_active', true)->count();
        $inactiveCount = Product::where('is_active', false)->count();

        return view('admin.inv.products.index', compact(
            'products',
            'q',
            'active',
            'stockMap',
            'lowStockCountPage',
            'expiredCount',
            'expiringSoonCount',
            'nearestExpirationMap',
            'nearestLotMap',
            'activeCount',
            'inactiveCount'
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
        $presentationUnits = ProductPresentationUnit::where('is_active', true)->orderBy('name')->get();
        $measurementUnits  = MeasurementUnit::where('is_active', true)->orderBy('name')->get();
        $suppliers         = Supplier::where('active', true)->orderBy('name')->get();

        // Transformar a formato JSON para el buscador modal
        $categoriesJson = $categories->map(fn($c) => ['id' => $c->id, 'label' => $c->name, 'sub' => $c->code])->values();
        $presentationUnitsJson = $presentationUnits->map(fn($u) => ['id' => $u->id, 'label' => $u->name, 'sub' => $u->short_name])->values();
        $measurementUnitsJson = $measurementUnits->map(fn($u) => ['id' => $u->id, 'label' => $u->name, 'sub' => $u->symbol])->values();
        $suppliersJson = $suppliers->map(fn($s) => ['id' => $s->id, 'label' => $s->name, 'sub' => $s->tax_id])->values();

        return view('admin.inv.products.create', compact(
            'product',
            'categories',
            'presentationUnits',
            'measurementUnits',
            'suppliers',
            'categoriesJson',
            'presentationUnitsJson',
            'measurementUnitsJson',
            'suppliersJson'
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
            ->route('admin.inv.products.index')
            ->with('ok', 'Producto creado');
    }

    public function edit(Product $product)
    {
        $categories        = ProductCategory::orderBy('name')->get();
        $presentationUnits = ProductPresentationUnit::where('is_active', true)->orderBy('name')->get();
        $measurementUnits  = MeasurementUnit::where('is_active', true)->orderBy('name')->get();
        $suppliers         = Supplier::where('active', true)->orderBy('name')->get();

        $batches = InventoryMovement::selectRaw('lot, MAX(expires_at) as expires_at, SUM(CASE WHEN type="out" THEN -qty ELSE qty END) as stock')
            ->where('product_id', $product->id)
            ->groupBy('lot')
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

        // Transformar a formato JSON para el buscador modal
        $categoriesJson = $categories->map(fn($c) => ['id' => $c->id, 'label' => $c->name, 'sub' => $c->code])->values();
        $presentationUnitsJson = $presentationUnits->map(fn($u) => ['id' => $u->id, 'label' => $u->name, 'sub' => $u->short_name])->values();
        $measurementUnitsJson = $measurementUnits->map(fn($u) => ['id' => $u->id, 'label' => $u->name, 'sub' => $u->symbol])->values();
        $suppliersJson = $suppliers->map(fn($s) => ['id' => $s->id, 'label' => $s->name, 'sub' => $s->tax_id])->values();

        return view('admin.inv.products.edit', compact(
            'product',
            'categories',
            'presentationUnits',
            'measurementUnits',
            'suppliers',
            'batches',
            'categoriesJson',
            'presentationUnitsJson',
            'measurementUnitsJson',
            'suppliersJson'
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

        return redirect()
            ->route('admin.inv.products.index')
            ->with('ok', 'Producto actualizado');
    }

    public function toggle(Product $product)
    {
        // Alternar el estado activo/inactivo
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activado' : 'desactivado';
        return back()->with('ok', "Producto $status correctamente.");
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
            ->where(function($q) use ($currentLot) {
                if (empty($currentLot)) {
                    $q->whereNull('lot')->orWhere('lot', '');
                } else {
                    $q->where('lot', $currentLot);
                }
            })
            ->update([
                'lot'        => $newLot,
                'expires_at' => $expiresAt
            ]);

        return back()->with('ok', 'Lote actualizado correctamente.');
    }
}
