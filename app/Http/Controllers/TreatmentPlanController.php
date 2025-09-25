<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Service;
use App\Models\Treatment;
use App\Models\TreatmentPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TreatmentPlanController extends Controller
{
    // Lista de planes por paciente
    public function index(Patient $patient)
    {
        $plans = TreatmentPlan::where('patient_id', $patient->id)
            ->latest()->paginate(12);

        return view('admin.plans.index', compact('patient', 'plans'));
    }

    public function create(Patient $patient)
    {
        return view('admin.plans.create', compact('patient'));
    }

    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
        ]);

        $plan = TreatmentPlan::create([
            'patient_id'     => $patient->id,
            'title'          => $data['title'],
            'estimate_total' => 0,
            'status'         => 'draft',
        ]);

        return redirect()->route('admin.plans.edit', $plan)
            ->with('ok', 'Plan creado. Agrega tratamientos.');
    }

    public function show(TreatmentPlan $plan)
    {
        // mostrar solo-lectura (si quisieras)
        $plan->load(['patient', 'treatments.service']);
        return view('admin.plans.show', compact('plan'));
    }

    public function edit(TreatmentPlan $plan)
    {
        $plan->load(['treatments.service']); // para nombre del servicio
        $services = Service::orderBy('name')->get(['id', 'name', 'price']);
        return view('admin.plans.edit', compact('plan', 'services'));
    }


    public function update(Request $request, TreatmentPlan $plan)
    {
        $data = $request->validate([
            'title'  => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:draft,approved,in_progress'],
        ]);

        $plan->update($data);
        return back()->with('ok', 'Plan actualizado.');
    }

    public function destroy(TreatmentPlan $plan)
    {
        $patient = $plan->patient;
        $plan->delete();
        return redirect()->route('admin.patients.plans.index', $patient)->with('ok', 'Plan eliminado.');
    }

    public function approve(TreatmentPlan $plan)
    {
        $plan->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => optional(auth()->user())->id,
        ]);

        return back()->with('ok', 'Plan aprobado.');
    }

    public function start(TreatmentPlan $plan)
    {
        $plan->update(['status' => 'in_progress']);
        return back()->with('ok', 'Plan en ejecución.');
    }

    // recalcula estimate_total = suma de items
    public function recalc(TreatmentPlan $plan)
    {
        $sum = $plan->treatments()->sum('price');
        $plan->update(['estimate_total' => $sum]);
        return back()->with('ok', 'Total recalculado.');
    }

    public function print(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'treatments.service', 'approver']);
        return view('admin.plans.print', compact('plan'));
    }

    public function pdf(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'treatments.service', 'approver']);

        // Si tienes instalado barryvdh/laravel-dompdf
        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('admin.plans.print', ['plan' => $plan]);
            $filename = 'plan_' . $plan->id . '.pdf';
            return $pdf->download($filename);
        }

        return redirect()->route('admin.plans.print', $plan)
            ->with('warn', 'Para PDF instala barryvdh/laravel-dompdf. Te abrí la vista imprimible.');
    }
}
