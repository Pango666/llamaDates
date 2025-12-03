<?php

namespace App\Http\Controllers;

use App\Models\ProductPresentationUnit;
use Illuminate\Http\Request;

class ProductPresentationUnitController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));

        $units = ProductPresentationUnit::when($q, function ($qq) use ($q) {
            $qq->where('name', 'like', "%$q%")
               ->orWhere('short_name', 'like', "%$q%");
        })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inv.presentation_units.index', compact('units', 'q'));
    }

    public function create()
    {
        $unit = new ProductPresentationUnit(['is_active' => true]);

        return view('admin.inv.presentation_units.create', compact('unit'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'       => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:30'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $unit = ProductPresentationUnit::create($data);

        return redirect()
            ->route('admin.inv.presentation-units.edit', $unit)
            ->with('ok', 'Unidad de presentación creada');
    }

    public function edit(ProductPresentationUnit $presentation_unit)
    {
        $unit = $presentation_unit;

        return view('admin.inv.presentation_units.edit', compact('unit'));
    }

    public function update(Request $r, ProductPresentationUnit $presentation_unit)
    {
        $unit = $presentation_unit;

        $data = $r->validate([
            'name'       => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:30'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $unit->update($data);

        return back()->with('ok', 'Unidad de presentación actualizada');
    }

    public function destroy(ProductPresentationUnit $presentation_unit)
    {
        $unit = $presentation_unit;

        if ($unit->products()->exists()) {
            return back()->withErrors('No se puede eliminar: la unidad de presentación tiene productos asociados.');
        }

        $unit->delete();

        return redirect()
            ->route('admin.inv.presentation-units.index')
            ->with('ok', 'Unidad de presentación eliminada');
    }
}
