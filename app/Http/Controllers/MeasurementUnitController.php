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
        $unit = new MeasurementUnit(['is_active' => true]);

        return view('admin.inv.measurement_units.create', compact('unit'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'      => ['required', 'string', 'max:120'],
            'symbol'    => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $unit = MeasurementUnit::create($data);

        return redirect()
            ->route('admin.inv.measurement-units.edit', $unit)
            ->with('ok', 'Unidad de medida creada');
    }

    public function edit(MeasurementUnit $measurement_unit)
    {
        $unit = $measurement_unit;

        return view('admin.inv.measurement_units.edit', compact('unit'));
    }

    public function update(Request $r, MeasurementUnit $measurement_unit)
    {
        $unit = $measurement_unit;

        $data = $r->validate([
            'name'      => ['required', 'string', 'max:120'],
            'symbol'    => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $unit->update($data);

        return back()->with('ok', 'Unidad de medida actualizada');
    }

    public function destroy(MeasurementUnit $measurement_unit)
    {
        $unit = $measurement_unit;

        if ($unit->products()->exists()) {
            return back()->withErrors('No se puede eliminar: la unidad de medida está asociada a productos.');
        }

        $unit->delete();

        return redirect()
            ->route('admin.inv.measurement-units.index')
            ->with('ok', 'Unidad de medida eliminada');
    }
}
