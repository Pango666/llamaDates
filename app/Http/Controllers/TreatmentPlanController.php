<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\Dentist;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreatmentPlanController extends Controller
{
    // Lista de planes por paciente
    public function index(Patient $patient)
    {
        $plans = TreatmentPlan::where('patient_id', $patient->id)
            ->with(['service', 'dentist'])
            ->latest()
            ->paginate(12);

        return view('admin.plans.index', compact('patient', 'plans'));
    }

    public function create(Patient $patient)
    {
        $services = Service::where('active', true)->orderBy('name')->get();
        $dentists = Dentist::with('user')->where('status', true)->get();
        return view('admin.plans.create', compact('patient', 'services', 'dentists'));
    }

    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'title'          => ['nullable', 'string', 'max:120'],
            'service_id'     => ['required', 'exists:services,id'],
            'dentist_id'     => ['required', 'exists:dentists,id'],
            'estimate_total' => ['required', 'numeric', 'min:0'],
            'total_sessions' => ['required', 'integer', 'min:1'],
            'tooth_code'     => ['nullable', 'string', 'max:3'],
            'surface'        => ['nullable', 'in:O,M,D,B,L,I'],
        ]);

        $service = Service::find($data['service_id']);
        $title = $data['title'] ?: "Plan de " . $service->name;

        $plan = TreatmentPlan::create([
            'patient_id'     => $patient->id,
            'service_id'     => $data['service_id'],
            'dentist_id'     => $data['dentist_id'],
            'tooth_code'     => $data['tooth_code'],
            'surface'        => $data['surface'],
            'title'          => $title,
            'estimate_total' => $data['estimate_total'],
            'total_sessions' => $data['total_sessions'],
            'status'         => 'draft',
        ]);

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('ok', 'Plan creado exitosamente.');
    }

    public function show(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'service', 'dentist']);

        return view('admin.plans.show', compact('plan'));
    }

    public function edit(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'service', 'dentist', 'appointments.dentist', 'invoices.payments']);
        
        // Cargar todos para que no se pierdan si el actual está inactivo
        $services = Service::orderBy('name')->get();
        $dentists = Dentist::with('user')->get();

        return view('admin.plans.edit', compact('plan', 'services', 'dentists'));
    }

    /**
     * Actualiza datos del plan.
     */
    public function update(Request $request, TreatmentPlan $plan)
    {
        $action = $request->input('action', 'save');

        $data = $request->validate([
            'title'          => ['nullable', 'string', 'max:120'],
            'status'         => ['required', 'in:draft,approved,in_progress,completed,canceled'],
            'service_id'     => ['required', 'exists:services,id'],
            'dentist_id'     => ['required', 'exists:dentists,id'],
            'estimate_total' => ['required', 'numeric', 'min:0'],
            'total_sessions' => ['required', 'integer', 'min:1'],
            'tooth_code'     => ['nullable', 'string', 'max:3'],
            'surface'        => ['nullable', 'in:O,M,D,B,L,I'],
        ]);

        $service = Service::find($data['service_id']);
        $title = $data['title'] ?: "Plan de " . $service->name;

        $plan->update([
            'title'          => $title,
            'status'         => $data['status'],
            'service_id'     => $data['service_id'],
            'dentist_id'     => $data['dentist_id'],
            'estimate_total' => $data['estimate_total'],
            'total_sessions' => $data['total_sessions'],
            'tooth_code'     => $data['tooth_code'],
            'surface'        => $data['surface'],
        ]);

        if ($action === 'approve' || $data['status'] === 'approved') {
            $plan->update([
                'status'      => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            return back()->with('ok', 'Plan aprobado. Ahora puedes programar las citas.');
        }

        return back()->with('ok', 'Plan actualizado.');
    }

    public function destroy(TreatmentPlan $plan)
    {
        $patient = $plan->patient;
        $plan->delete();

        return redirect()
            ->route('admin.patients.plans.index', $patient)
            ->with('ok', 'Plan eliminado.');
    }

    /**
     * Marca el plan "en ejecución".
     */
    public function start(TreatmentPlan $plan)
    {
        $plan->update(['status' => 'in_progress']);

        return back()->with('ok', 'Plan en ejecución.');
    }

    /**
     * Recalcula progreso.
     */
    public function recalc(TreatmentPlan $plan)
    {
        $plan->refreshProgress();
        return back()->with('ok', 'Progreso recalculado según las citas completadas.');
    }

    public function print(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'service', 'dentist', 'appointments.dentist', 'approver', 'invoices.payments']);

        return view('admin.plans.print', compact('plan'));
    }

    public function pdf(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'service', 'dentist', 'appointments.dentist', 'approver', 'invoices.payments']);

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('admin.plans.print', [
                'plan'  => $plan,
                'isPdf' => true,
            ]);
            $filename = 'plan_' . $plan->id . '.pdf';

            return $pdf->download($filename);
        }

        return redirect()
            ->route('admin.plans.print', $plan)
            ->with('warn', 'Para PDF instala barryvdh/laravel-dompdf. Te abrí la vista imprimible.');
    }
}
