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
            'notes'      => 'nullable|string',
        ]);

        // Si no mandas precio, usar el del servicio
        if (empty($data['price'])) {
            $service = Service::find($data['service_id']);
            $data['price'] = $service?->price ?? 0;
        }

        $data['treatment_plan_id'] = $plan->id;

        Treatment::create($data);

        // Actualizar total_sessions del plan
        $plan->update([
            'total_sessions' => $plan->treatments()->count(),
        ]);

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
     */
    public function update(Request $request, Treatment $treatment)
    {
        $plan = $treatment->plan;

        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'tooth_code' => 'nullable|string|max:3',
            'surface'    => 'nullable|in:O,M,D,B,L,I',
            'price'      => 'required|numeric|min:0',
            'status'     => 'required|in:planned,in_progress,done,canceled',
            'notes'      => 'nullable|string',
        ]);

        $treatment->update($data);

        // Actualizar progreso del plan
        if ($plan) {
            $plan->refreshProgress();
        }

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

        // Actualizar total_sessions del plan
        if ($plan) {
            $plan->update([
                'total_sessions' => $plan->treatments()->count(),
            ]);
        }

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('ok', 'Tratamiento eliminado.');
    }

    /**
     * Agendar cita desde un tratamiento.
     * Redirige al formulario de nueva cita pre-populado con datos del tratamiento.
     */
    public function schedule(Treatment $treatment)
    {
        $plan = $treatment->plan;

        if (!$plan) {
            return back()->with('error', 'Tratamiento no vinculado a un plan.');
        }

        // Redirigir al formulario de nueva cita con parámetros pre-poblados
        return redirect()->route('admin.appointments.create', [
            'patient_id'   => $plan->patient_id,
            'service_id'   => $treatment->service_id,
            'dentist_id'   => $treatment->dentist_id,
            'treatment_id' => $treatment->id,
            'plan_id'      => $plan->id,
        ]);
    }
}
