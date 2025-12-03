<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));

        $categories = ProductCategory::when($q, function ($qq) use ($q) {
            $qq->where('name', 'like', "%$q%")
               ->orWhere('code', 'like', "%$q%");
        })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inv.categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        $category = new ProductCategory(['is_active' => true]);

        return view('admin.inv.categories.create', compact('category'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'      => ['required', 'string', 'max:120'],
            'code'      => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $cat = ProductCategory::create($data);

        return redirect()
            ->route('admin.inv.categories.edit', $cat)
            ->with('ok', 'Categoría creada');
    }

    public function edit(ProductCategory $category)
    {
        return view('admin.inv.categories.edit', compact('category'));
    }

    public function update(Request $r, ProductCategory $category)
    {
        $data = $r->validate([
            'name'      => ['required', 'string', 'max:120'],
            'code'      => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $category->update($data);

        return back()->with('ok', 'Categoría actualizada');
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors('No se puede eliminar: la categoría tiene productos asociados.');
        }

        $category->delete();

        return redirect()
            ->route('admin.inv.categories.index')
            ->with('ok', 'Categoría eliminada');
    }
}
