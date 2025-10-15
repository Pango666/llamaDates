<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string)$r->get('q',''));
        $active = $r->get('active', 'all'); // all|1|0

        $products = Product::when($q, function($qq) use($q){
                $qq->where('name','like',"%$q%")
                   ->orWhere('sku','like',"%$q%");
            })
            ->when($active !== 'all', fn($qq)=>$qq->where('active', (int)$active))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        // stock por producto (todas las ubicaciones): sum(mov in/out)
        $stockMap = InventoryMovement::selectRaw('product_id, SUM(CASE WHEN type="in" THEN qty ELSE -qty END) as stock')
            ->groupBy('product_id')
            ->pluck('stock','product_id');

        return view('admin.inv.products.index', compact('products','q','active','stockMap'));
    }

    public function create()
    {
        $product = new Product(['active'=>true, 'sell_price'=>0, 'cost_avg'=>0, 'min_stock'=>0, 'unit'=>'und']);
        return view('admin.inv.products.create', compact('product'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'sku'        => ['nullable','string','max:60', Rule::unique('products','sku')],
            'name'       => ['required','string','max:120'],
            'unit'       => ['required','string','max:20'],
            'sell_price' => ['required','numeric','min:0'],
            'cost_avg'   => ['nullable','numeric','min:0'],
            'min_stock'  => ['nullable','numeric','min:0'],
            'active'     => ['nullable','boolean'],
        ]);

        $data['active']   = (bool)($data['active'] ?? true);
        $data['cost_avg'] = $data['cost_avg'] ?? 0;
        $data['min_stock']= $data['min_stock'] ?? 0;

        $p = Product::create($data);

        return redirect()->route('admin.inv.products.edit', $p)->with('ok','Producto creado');
    }

    public function edit(Product $product)
    {
        return view('admin.inv.products.edit', compact('product'));
    }

    public function update(Request $r, Product $product)
    {
        $data = $r->validate([
            'sku'        => ['nullable','string','max:60', Rule::unique('products','sku')],
            'name'       => ['required','string','max:120'],
            'unit'       => ['required','string','max:20'],
            'sell_price' => ['required','numeric','min:0'],
            'cost_avg'   => ['nullable','numeric','min:0'],
            'min_stock'  => ['nullable','numeric','min:0'],
            'active'     => ['nullable','boolean'],
        ]);

        $data['active']   = (bool)($data['active'] ?? false);
        $data['cost_avg'] = $data['cost_avg'] ?? 0;
        $data['min_stock']= $data['min_stock'] ?? 0;

        $product->update($data);

        return back()->with('ok','Producto actualizado');
    }

    public function destroy(Product $product)
    {
        // No permitir borrar si tiene movimientos
        $hasMovs = InventoryMovement::where('product_id',$product->id)->exists();
        if ($hasMovs) {
            return back()->withErrors('No se puede eliminar: el producto tiene movimientos de inventario. Desactívalo en su lugar.');
        }
        $product->delete();
        return redirect()->route('admin.inv.products.index')->with('ok','Producto eliminado');
    }
}
