<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $q     = trim((string) $request->get('q', ''));
        $state = $request->get('state', 'all'); // all|active|inactive
        $sort  = $request->get('sort', 'name'); // name|price|duration|created_at

        $sortMap = [
            'name'       => ['column' => 'name',         'dir' => 'asc'],
            'price'      => ['column' => 'price',        'dir' => 'asc'],
            'duration'   => ['column' => 'duration_min', 'dir' => 'asc'],
            'created_at' => ['column' => 'created_at',   'dir' => 'desc'],
        ];
        $orderCol = $sortMap[$sort]['column'] ?? 'name';
        $orderDir = $sortMap[$sort]['dir']    ?? 'asc';

        $services = Service::query()
            ->when($q !== '', fn($qq) => $qq->where('name', 'like', "%{$q}%"))
            ->when($state !== 'all', fn($qq) => $qq->where('active', $state === 'active'))
            ->orderBy($orderCol, $orderDir)
            ->paginate(9)
            ->withQueryString();

        $activeCount   = Service::where('active', 1)->count();
        $inactiveCount = Service::where('active', 0)->count();
        $averagePrice  = Service::where('active', 1)->avg('price');

        return view('admin.services.index', compact(
            'services',
            'q',
            'state',
            'sort',
            'activeCount',
            'inactiveCount',
            'averagePrice'
        ));
    }

    public function create()
    {
        $service = new Service(['active' => true, 'duration_min' => 30]);
        return view('admin.services.create', compact('service'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150', 'unique:services,name'],
            'duration_min' => ['required', 'integer', 'min:5', 'max:480'],
            'price'        => ['required', 'numeric', 'min:0', 'max:9999999'],
            'active'       => ['nullable', 'boolean'],

            'discount_active'    => ['nullable', 'boolean'],
            'discount_type'      => ['nullable', Rule::in(['percent', 'fixed'])],
            'discount_amount'    => ['nullable', 'numeric', 'min:0'],
            'discount_duration'  => ['nullable', 'integer', 'min:1', 'max:3650'],
            'discount_starts_at' => ['nullable', 'date'],
            'discount_ends_at'   => ['nullable', 'date', 'after_or_equal:discount_starts_at'],
        ]);

        $data['active'] = (bool) ($data['active'] ?? false);

        $data['discount_active'] = (bool) ($data['discount_active'] ?? false);

        if (!$data['discount_active']) {
            $data['discount_type']      = null;
            $data['discount_amount']    = null;
            $data['discount_duration']  = null;
            $data['discount_starts_at'] = null;
            $data['discount_ends_at']   = null;
        } else {
            $data['discount_type'] = $data['discount_type'] ?? 'percent';

            // Normaliza amount
            $amount = (float) ($data['discount_amount'] ?? 0);

            // Si es porcentaje, clamp 0..100
            if ($data['discount_type'] === 'percent') {
                $amount = max(0, min(100, $amount));
            } else {
                // fixed: no puede superar el precio
                $price = (float) ($data['price'] ?? 0);
                $amount = max(0, min($price, $amount));
            }
            $data['discount_amount'] = $amount;

            // Fechas automáticas
            $hasStart = !empty($data['discount_starts_at']);
            $hasEnd   = !empty($data['discount_ends_at']);
            $days     = (int) ($data['discount_duration'] ?? 0);

            // Si NO llega start, lo pongo ahora
            if (!$hasStart) {
                $data['discount_starts_at'] = now();
                $hasStart = true;
            }

            // Si NO llega end, y tengo duration, calculo end
            if (!$hasEnd && $days > 0) {
                $data['discount_ends_at'] = \Carbon\Carbon::parse($data['discount_starts_at'])->addDays($days);
            }
        }

        Service::create($data);

        return redirect()->route('admin.services')->with('ok', 'Servicio creado.');
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150', Rule::unique('services', 'name')->ignore($service->id)],
            'duration_min' => ['required', 'integer', 'min:5', 'max:480'],
            'price'        => ['required', 'numeric', 'min:0', 'max:9999999'],
            'active'       => ['nullable', 'boolean'],

            'discount_active'    => ['nullable', 'boolean'],
            'discount_type'      => ['nullable', Rule::in(['percent', 'fixed'])],
            'discount_amount'    => ['nullable', 'numeric', 'min:0'],
            'discount_duration'  => ['nullable', 'integer', 'min:1', 'max:3650'],
            'discount_starts_at' => ['nullable', 'date'],
            'discount_ends_at'   => ['nullable', 'date', 'after_or_equal:discount_starts_at'],
        ]);

        $data['active'] = (bool) ($data['active'] ?? false);

        $data['discount_active'] = (bool) ($data['discount_active'] ?? false);

        if (!$data['discount_active']) {
            $data['discount_type']      = null;
            $data['discount_amount']    = null;
            $data['discount_duration']  = null;
            $data['discount_starts_at'] = null;
            $data['discount_ends_at']   = null;
        } else {
            $data['discount_type'] = $data['discount_type'] ?? ($service->discount_type ?? 'percent');

            $amount = (float) ($data['discount_amount'] ?? ($service->discount_amount ?? 0));

            if ($data['discount_type'] === 'percent') {
                $amount = max(0, min(100, $amount));
            } else {
                $price = (float) ($data['price'] ?? $service->price ?? 0);
                $amount = max(0, min($price, $amount));
            }
            $data['discount_amount'] = $amount;

            $hasStart = !empty($data['discount_starts_at']);
            $hasEnd   = !empty($data['discount_ends_at']);
            $days     = (int) ($data['discount_duration'] ?? 0);

            // Si ya existía un start y no lo mandan, lo conservo
            if (!$hasStart) {
                $data['discount_starts_at'] = $service->discount_starts_at ?? now();
                $hasStart = true;
            }

            // Si no mandan end, y duration existe, calculo desde start
            if (!$hasEnd && $days > 0) {
                $data['discount_ends_at'] = \Carbon\Carbon::parse($data['discount_starts_at'])->addDays($days);
            }

            // Si mandan end manual, respeta end. Si duration está vacío, ok.
        }

        $service->update($data);

        return redirect()->route('admin.services')->with('ok', 'Servicio actualizado.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }


    public function toggle(Service $service)
    {
        $service->update(['active' => ! $service->active]);
        return back()->with('ok', $service->active ? 'Servicio activado.' : 'Servicio desactivado.');
    }

    public function destroy(Service $service)
    {
        if (! $service->active) {
            return back()->with('ok', 'El servicio ya estaba desactivado.');
        }

        $service->update(['active' => false]);

        return redirect()->route('admin.services')->with('ok', 'Servicio desactivado.');
    }


    private function validateService(Request $request, ?Service $service): array
    {
        $uniqueName = $service
            ? Rule::unique('services', 'name')->ignore($service->id)
            : 'unique:services,name';

        return $request->validate([
            'name'         => ['required', 'string', 'max:150', $uniqueName],
            'duration_min' => ['required', 'integer', 'min:5', 'max:480'],
            'price'        => ['required', 'numeric', 'min:0', 'max:9999999'],
            'active'       => ['nullable', 'boolean'],

            'discount_active'    => ['nullable', 'boolean'],
            'discount_type'      => ['nullable', 'in:percent,fixed'],
            'discount_amount'    => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'discount_duration'  => ['nullable', 'integer', 'min:1', 'max:3650'],
            'discount_starts_at' => ['nullable', 'date'],
            'discount_ends_at'   => ['nullable', 'date', 'after_or_equal:discount_starts_at'],
        ]);
    }

    private function normalizeDiscount(array $data, ?Service $service): array
    {
        $data['active'] = (bool) ($data['active'] ?? false);
        $data['discount_active'] = (bool) ($data['discount_active'] ?? false);

        // Si descuento apagado: limpiar todo y salir
        if (!$data['discount_active']) {
            $data['discount_type'] = null;
            $data['discount_amount'] = null;
            $data['discount_duration'] = null;
            $data['discount_starts_at'] = null;
            $data['discount_ends_at'] = null;
            return $data;
        }

        // Si lo activaron pero no llenaron lo mínimo, lo apagamos para no dejar basura
        $type = $data['discount_type'] ?? null;
        $amount = $data['discount_amount'] ?? null;

        if (!$type || $amount === null || $amount === '') {
            $data['discount_active'] = false;
            $data['discount_type'] = null;
            $data['discount_amount'] = null;
            $data['discount_duration'] = null;
            $data['discount_starts_at'] = null;
            $data['discount_ends_at'] = null;
            return $data;
        }

        // Normaliza amount
        $amount = (float) $amount;

        // Clamp percent
        if ($type === 'percent') {
            if ($amount < 0) $amount = 0;
            if ($amount > 100) $amount = 100;
        }

        $data['discount_type'] = $type;
        $data['discount_amount'] = $amount;

        // Start: si no mandan, mantener el existente en update; si no existe, now()
        $start = !empty($data['discount_starts_at'])
            ? Carbon::parse($data['discount_starts_at'])
            : ($service?->discount_starts_at ?: now());

        $data['discount_starts_at'] = $start;

        // End: si mandan, se respeta
        if (!empty($data['discount_ends_at'])) {
            $data['discount_ends_at'] = Carbon::parse($data['discount_ends_at']);
            return $data;
        }

        // Si no mandan end, pero sí duración: calcular end
        $dur = $data['discount_duration'] ?? null;
        if ($dur !== null && (int)$dur > 0) {
            $data['discount_duration'] = (int) $dur;
            $data['discount_ends_at'] = $start->copy()->addDays((int)$dur)->endOfDay();
        } else {
            // Sin fin y sin duración: descuento indefinido hasta desactivar
            $data['discount_duration'] = $dur !== null ? (int)$dur : null;
            $data['discount_ends_at'] = null;
        }

        return $data;
    }
}
