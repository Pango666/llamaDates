<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Treatment;
use App\Models\TreatmentPlan;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    public function store(TreatmentPlan $plan, Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'tooth_code' => ['nullable', 'string', 'max:3'],
            'surface'    => ['nullable', 'in:O,M,D,B,L'],
            'price'      => ['nullable', 'numeric', 'min:0'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $data['treatment_plan_id'] = $plan->id;
        $data['status'] = 'planned';
        if (!isset($data['price']) || $data['price'] === '') {
            $svc = Service::find($data['service_id']);
            $data['price'] = $svc?->price ?? 0;
        }

        Treatment::create($data);

        return back()->with('ok', 'Tratamiento agregado');
    }

    public function edit(TreatmentPlan $plan, Treatment $treatment)
    {
        $services = Service::orderBy('name')->get(['id', 'name', 'price']);
        return view('admin.plans.treatments.edit', compact('plan', 'treatment', 'services'));
    }

    public function update(TreatmentPlan $plan, Treatment $treatment, Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'tooth_code' => ['nullable', 'string', 'max:3'],
            'surface'    => ['nullable', 'in:O,M,D,B,L'],
            'price'      => ['required', 'numeric', 'min:0'],
            'status'     => ['required', 'in:planned,in_progress,done,canceled'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $treatment->update($data);

        return redirect()->route('admin.plans.edit', $plan)->with('ok', 'Tratamiento actualizado');
    }

    public function destroy(TreatmentPlan $plan, Treatment $treatment)
    {
        $treatment->delete();
        return back()->with('ok', 'Tratamiento eliminado');
    }

    public function schedule(Treatment $treatment)
    {
        $plan = $treatment->plan ?? $treatment->treatmentPlan; // según tu relación
        $patientId = $plan?->patient_id;

        // Construimos un “contexto” para precargar la cita
        $q = array_filter([
            'patient_id' => $patientId,
            'service_id' => $treatment->service_id,
            'dentist_id' => null, // si quieres preasignar alguno
            'date'       => now()->toDateString(), // sugerencia
            'notes'      => trim('Desde plan #' . $plan->id .
                ($treatment->tooth_code ? ' · pieza ' . $treatment->tooth_code : '') .
                ($treatment->surface ? ' ' . $treatment->surface : '')),
        ]);

        return redirect()->route('admin.appointments.create', $q);
    }
}
