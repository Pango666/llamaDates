<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            ->paginate(15)
            ->withQueryString();

        // KPIs
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


    /** Form crear */
    public function create()
    {
        $service = new Service(['active' => true, 'duration_min' => 30]);
        return view('admin.services.create', compact('service'));
    }

    /** Guardar */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150', 'unique:services,name'],
            'duration_min' => ['required', 'integer', 'min:5', 'max:480'],
            'price'        => ['required', 'numeric', 'min:0', 'max:9999999'],
            'active'       => ['nullable', 'boolean'],
        ]);
        $data['active'] = (bool) ($data['active'] ?? false);

        Service::create($data);

        return redirect()->route('admin.services')->with('ok', 'Servicio creado.');
    }

    /** Form editar */
    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    /** Actualizar */
    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150', Rule::unique('services', 'name')->ignore($service->id)],
            'duration_min' => ['required', 'integer', 'min:5', 'max:480'],
            'price'        => ['required', 'numeric', 'min:0', 'max:9999999'],
            'active'       => ['nullable', 'boolean'],
        ]);
        $data['active'] = (bool) ($data['active'] ?? false);

        $service->update($data);

        return redirect()->route('admin.services')->with('ok', 'Servicio actualizado.');
    }

    /** Activar/Desactivar */
    public function toggle(Service $service)
    {
        $service->update(['active' => ! $service->active]);
        return back()->with('ok', $service->active ? 'Servicio activado.' : 'Servicio desactivado.');
    }

    /** Eliminar (bloquea si está en uso por citas o tratamientos) */
    public function destroy(Service $service)
    {
        $inUse = Appointment::where('service_id', $service->id)->exists()
            || Treatment::where('service_id', $service->id)->exists();

        if ($inUse) {
            return back()->withErrors('No se puede eliminar: el servicio está en uso.');
        }

        $service->delete();

        return redirect()->route('admin.services')->with('ok', 'Servicio eliminado.');
    }
}
