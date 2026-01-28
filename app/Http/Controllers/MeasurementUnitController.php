<?php

namespace App\Http\Controllers;

use App\Models\MeasurementUnit;
use Illuminate\Http\Request;

class MeasurementUnitController extends Controller
{
    public function index(Request $r)
    {
        $q      = trim((string) $r->get('q', ''));
        $active = $r->get('active', 'all');

        $units = MeasurementUnit::when($q, function ($qq) use ($q) {
            $qq->where('name', 'like', "%$q%")
               ->orWhere('symbol', 'like', "%$q%");
        })
            ->when($active !== 'all', fn ($qy) => $qy->where('is_active', (int)$active))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $activeCount   = MeasurementUnit::where('is_active', true)->count();
        $inactiveCount = MeasurementUnit::where('is_active', false)->count();

        return view('admin.inv.measurement_units.index', compact('units', 'q', 'active', 'activeCount', 'inactiveCount'));
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

        MeasurementUnit::create($data);

        return redirect()
            ->route('admin.inv.measurement_units.index')
            ->with('ok', 'Unidad de medida creada correctamente');
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

        return redirect()
            ->route('admin.inv.measurement_units.index')
            ->with('ok', 'Unidad de medida actualizada correctamente');
    }

    public function toggle(MeasurementUnit $measurementUnit)
    {
        $measurementUnit->update([
            'is_active' => !$measurementUnit->is_active
        ]);

        $status = $measurementUnit->is_active ? 'activada' : 'desactivada';
        return back()->with('ok', "Unidad $status correctamente.");
    }
}
