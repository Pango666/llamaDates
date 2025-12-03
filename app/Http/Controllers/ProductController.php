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

        // Contar productos con stock bajo en la página
        $lowStockCountPage = $products->getCollection()->filter(function ($p) use ($stockMap) {
            $stock = (float) ($stockMap[$p->id] ?? 0);
            $min   = (float) $p->min_stock;
            return $min > 0 && $stock <= $min;
        })->count();

        return view('admin.inv.products.index', compact(
            'products',
            'q',
            'active',
            'stockMap',
            'lowStockCountPage'
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
            'min_stock'             => ['nullable', 'numeric', 'min:0'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['min_stock'] = $data['min_stock'] ?? 0;

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

        return view('admin.inv.products.edit', compact(
            'product',
            'categories',
            'presentationUnits',
            'measurementUnits',
            'suppliers'
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
            'min_stock'             => ['nullable', 'numeric', 'min:0'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        // Si no viene el checkbox => false
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['min_stock'] = $data['min_stock'] ?? 0;

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
}
