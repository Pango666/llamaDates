<?php

namespace App\Http\Controllers;

use App\Models\TreatmentPlan;
use App\Models\Treatment;
use App\Models\Service;
use App\Models\Dentist;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    /**
     * Guardar un nuevo tratamiento dentro de un plan
     * Route: POST plans/{plan}/treatments
     */
    public function store(Request $request, TreatmentPlan $plan)
    {
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'tooth_code' => 'nullable|string|max:3',
            'surface'    => 'nullable|in:O,M,D,B,L,I',
            'price'      => 'nullable|numeric|min:0',

            // planificación opcional (todavía no citas reales)
            'dentist_id'           => 'nullable|exists:dentists,id',
            'planned_date'         => 'nullable|date|after_or_equal:today',
            'planned_start_time'   => 'nullable|date_format:H:i',
            'planned_end_time'     => 'nullable|date_format:H:i',
        ]);

        // Si no mandas precio, usar el del servicio
        if (empty($data['price'])) {
            $service = Service::find($data['service_id']);
            $data['price'] = $service?->price ?? 0;
        }

        $data['treatment_plan_id'] = $plan->id;

        Treatment::create($data);

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('ok', 'Tratamiento agregado al plan.');
    }

    /**
     * Editar un tratamiento
     * Route: GET plans/{plan}/treatments/{treatment}/edit
     */
    public function edit(TreatmentPlan $plan, Treatment $treatment)
    {
        $services = Service::orderBy('name')->get(['id', 'name']);
        $dentists = Dentist::orderBy('name')->get(['id', 'name']);

        return view('admin.plans.treatments.edit', [
            'plan'      => $plan,
            'treatment' => $treatment,
            'services'  => $services,
            'dentists'  => $dentists,
        ]);
    }

    /**
     * Actualizar un tratamiento
     * Route: PUT treatments/{treatment}
     * (el plan se obtiene desde la relación)
     */
    public function update(Request $request, Treatment $treatment)
    {
        $plan = $treatment->plan; // para redirigir luego

        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'tooth_code' => 'nullable|string|max:3',
            'surface'    => 'nullable|in:O,M,D,B,L,I',
            'price'      => 'required|numeric|min:0',
            'status'     => 'required|in:planned,in_progress,done,canceled',
            'notes'      => 'nullable|string',

            // planificación
            'dentist_id'           => 'nullable|exists:dentists,id',
            'planned_date'         => 'nullable|date|after_or_equal:today',
            'planned_start_time'   => 'nullable|date_format:H:i',
            'planned_end_time'     => 'nullable|date_format:H:i',
        ]);

        $treatment->update($data);

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('ok', 'Tratamiento actualizado.');
    }

    /**
     * Eliminar tratamiento
     * Route: DELETE treatments/{treatment}
     */
    public function destroy(Treatment $treatment)
    {
        $plan = $treatment->plan;
        $treatment->delete();

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('ok', 'Tratamiento eliminado.');
    }
}
