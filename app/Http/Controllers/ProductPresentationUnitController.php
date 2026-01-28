<?php

namespace App\Http\Controllers;

use App\Models\ProductPresentationUnit;
use Illuminate\Http\Request;

class ProductPresentationUnitController extends Controller
{
    public function index(Request $r)
    {
        $q      = trim((string) $r->get('q', ''));
        $active = $r->get('active', 'all');

        $units = ProductPresentationUnit::when($q, function ($qq) use ($q) {
            $qq->where('name', 'like', "%$q%")
               ->orWhere('short_name', 'like', "%$q%");
        })
            ->when($active !== 'all', fn ($qy) => $qy->where('is_active', (int)$active))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $activeCount   = ProductPresentationUnit::where('is_active', true)->count();
        $inactiveCount = ProductPresentationUnit::where('is_active', false)->count();

        return view('admin.inv.presentation_units.index', compact('units', 'q', 'active', 'activeCount', 'inactiveCount'));
    }

    public function create()
    {
        $presentationUnit = new ProductPresentationUnit(['is_active' => true]);
        return view('admin.inv.presentation_units.create', compact('presentationUnit'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'       => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:30'],
            'is_active'  => ['nullable', 'boolean'],
            'description'=> ['nullable', 'string', 'max:500'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        ProductPresentationUnit::create($data);

        return redirect()
            ->route('admin.inv.presentation_units.index')
            ->with('ok', 'Unidad de presentación creada correctamente');
    }

    public function edit(ProductPresentationUnit $presentationUnit)
    {
        return view('admin.inv.presentation_units.edit', compact('presentationUnit'));
    }

    public function update(Request $r, ProductPresentationUnit $presentationUnit)
    {
        $data = $r->validate([
            'name'       => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:30'],
            'is_active'  => ['nullable', 'boolean'],
            'description'=> ['nullable', 'string', 'max:500'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $presentationUnit->update($data);

        return redirect()
            ->route('admin.inv.presentation_units.index')
            ->with('ok', 'Unidad de presentación actualizada correctamente');
    }

    public function toggle(ProductPresentationUnit $presentationUnit)
    {
        $presentationUnit->update([
            'is_active' => !$presentationUnit->is_active
        ]);

        $status = $presentationUnit->is_active ? 'activada' : 'desactivada';
        return back()->with('ok', "Unidad $status correctamente.");
    }

    public function destroy(ProductPresentationUnit $presentationUnit)
    {
        if ($presentationUnit->products()->exists()) {
            return back()->withErrors('No se puede eliminar: la unidad de presentación tiene productos asociados.');
        }

        $presentationUnit->delete();

        return redirect()
            ->route('admin.inv.presentation_units.index')
            ->with('ok', 'Unidad de presentación eliminada');
    }
}
