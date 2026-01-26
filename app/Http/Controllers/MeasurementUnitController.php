<?php

namespace App\Http\Controllers;

use App\Models\MeasurementUnit;
use Illuminate\Http\Request;

class MeasurementUnitController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));

        $units = MeasurementUnit::when($q, function ($qq) use ($q) {
            $qq->where('name', 'like', "%$q%")
               ->orWhere('symbol', 'like', "%$q%");
        })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inv.measurement_units.index', compact('units', 'q'));
    }

    public function create()
    {
        $measurementUnit = new MeasurementUnit(['is_active' => true]);

        return view('admin.inv.measurement_units.create', compact('measurementUnit'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'      => ['required', 'string', 'max:120'],
            'symbol'    => ['required', 'string', 'max:30'],
            'type'      => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $measurementUnit = MeasurementUnit::create($data);

        return redirect()
            ->route('admin.inv.measurement_units.edit', $measurementUnit)
            ->with('ok', 'Unidad de medida creada');
    }

    public function edit(MeasurementUnit $measurementUnit)
    {
        return view('admin.inv.measurement_units.edit', compact('measurementUnit'));
    }

    public function update(Request $r, MeasurementUnit $measurementUnit)
    {
        $data = $r->validate([
            'name'      => ['required', 'string', 'max:120'],
            'symbol'    => ['required', 'string', 'max:30'],
            'type'      => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $measurementUnit->update($data);

        return back()->with('ok', 'Unidad de medida actualizada');
    }

    public function destroy(MeasurementUnit $measurementUnit)
    {
        if ($measurementUnit->products()->exists()) {
            return back()->withErrors('No se puede eliminar: la unidad de medida está asociada a productos.');
        }

        $measurementUnit->delete();

        return redirect()
            ->route('admin.inv.measurement_units.index')
            ->with('ok', 'Unidad de medida eliminada');
    }
}
