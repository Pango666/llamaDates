<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{

    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));
        $active = $r->get('active', 'all'); // all|1|0

        $products = Product::when($q, function ($qq) use ($q) {
            $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('sku', 'like', "%$q%");
            });
        })
            // 👇 usar la columna correcta
            ->when($active !== 'all', fn($qq) => $qq->where('is_active', (int) $active))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Si solo necesitas stock de la página (mejor rendimiento):
        $pageIds = $products->getCollection()->pluck('id');

        $stockMap = InventoryMovement::selectRaw(
            'product_id, SUM(CASE WHEN type="in" THEN qty ELSE -qty END) as stock'
        )
            ->whereIn('product_id', $pageIds) // quitar si quieres global
            ->groupBy('product_id')
            ->pluck('stock', 'product_id');

        // Ejemplo: contar low stock en la página actual (castea a num porque vienen strings "120.000")
        $lowStockCountPage = $products->getCollection()->filter(function ($p) use ($stockMap) {
            $stock = (float) ($stockMap[$p->id] ?? 0);
            $min   = (float) $p->min_stock;
            return $min > 0 && $stock <= $min;
        })->count();

        // // Logs correctos
        // Log::info('inv.products.index params', ['q' => $q, 'active' => $active]);
        // Log::info('inv.products.index products', [
        //     'total'        => $products->total(),
        //     'per_page'     => $products->perPage(),
        //     'current_page' => $products->currentPage(),
        //     'ids_in_page'  => $pageIds->all(),
        // ]);
        // Log::info('inv.products.index stockMap', [
        //     'count'  => $stockMap->count(),
        //     'sample' => array_slice($stockMap->toArray(), 0, 10, true),
        // ]);

        return view('admin.inv.products.index', compact('products', 'q', 'active', 'stockMap', 'lowStockCountPage'));
    }

    public function create()
    {
        $product = new Product(['active' => true, 'sell_price' => 0, 'cost_avg' => 0, 'min_stock' => 0, 'unit' => 'und']);
        return view('admin.inv.products.create', compact('product'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'sku'        => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')],
            'name'       => ['required', 'string', 'max:120'],
            'unit'       => ['required', 'string', 'max:20'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'cost_avg'   => ['nullable', 'numeric', 'min:0'],
            'min_stock'  => ['nullable', 'numeric', 'min:0'],
            'active'     => ['nullable', 'boolean'],
        ]);

        $data['active']   = (bool)($data['active'] ?? true);
        $data['cost_avg'] = $data['cost_avg'] ?? 0;
        $data['min_stock'] = $data['min_stock'] ?? 0;

        $p = Product::create($data);

        return redirect()->route('admin.inv.products.edit', $p)->with('ok', 'Producto creado');
    }

    public function edit(Product $product)
    {
        return view('admin.inv.products.edit', compact('product'));
    }

    public function update(Request $r, Product $product)
    {
        $data = $r->validate([
            'sku'        => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')],
            'name'       => ['required', 'string', 'max:120'],
            'unit'       => ['required', 'string', 'max:20'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'cost_avg'   => ['nullable', 'numeric', 'min:0'],
            'min_stock'  => ['nullable', 'numeric', 'min:0'],
            'active'     => ['nullable', 'boolean'],
        ]);

        $data['active']   = (bool)($data['active'] ?? false);
        $data['cost_avg'] = $data['cost_avg'] ?? 0;
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
        return redirect()->route('admin.inv.products.index')->with('ok', 'Producto eliminado');
    }
}
