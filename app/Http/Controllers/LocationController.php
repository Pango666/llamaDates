<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));

        $locations = Location::when($q, function ($qq) use ($q) {
            $qq->where('name', 'like', "%$q%");
        })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inv.locations.index', compact('locations', 'q'));
    }

    public function create()
    {
        $location = new Location(['is_active' => true]);

        return view('admin.inv.locations.create', compact('location'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'      => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $loc = Location::create($data);

        return redirect()
            ->route('admin.inv.locations.edit', $loc)
            ->with('ok', 'Ubicación creada');
    }

    public function edit(Location $ubicacione)
    {
        $location = $ubicacione;

        return view('admin.inv.locations.edit', compact('location'));
    }

    public function update(Request $r, Location $ubicacione)
    {
        $location = $ubicacione;

        $data = $r->validate([
            'name'      => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $location->update($data);

        return back()->with('ok', 'Ubicación actualizada');
    }

    public function destroy(Location $ubicacione)
    {
        $location = $ubicacione;

        $hasMovs = InventoryMovement::where('location_id', $location->id)->exists();
        if ($hasMovs) {
            return back()->withErrors('No se puede eliminar: la ubicación tiene movimientos asociados. Desactívala en su lugar.');
        }

        $location->delete();

        return redirect()
            ->route('admin.inv.locations.index')
            ->with('ok', 'Ubicación eliminada');
    }
}
