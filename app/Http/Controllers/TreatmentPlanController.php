<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Service;
use App\Models\Treatment;
use App\Models\TreatmentPlan;
use App\Models\Dentist;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreatmentPlanController extends Controller
{
    // Lista de planes por paciente
    public function index(Patient $patient)
    {
        $plans = TreatmentPlan::where('patient_id', $patient->id)
            ->withCount('treatments')
            ->latest()
            ->paginate(12);

        return view('admin.plans.index', compact('patient', 'plans'));
    }

    public function create(Patient $patient)
    {
        return view('admin.plans.create', compact('patient'));
    }

    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:120'],
            'total_sessions' => ['nullable', 'integer', 'min:0'],
        ]);

        $plan = TreatmentPlan::create([
            'patient_id'     => $patient->id,
            'title'          => $data['title'],
            'total_sessions' => $data['total_sessions'] ?? 0,
            'estimate_total' => 0,
            'status'         => 'draft',
        ]);

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('ok', 'Plan creado. Agrega tratamientos.');
    }

    public function show(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'treatments.service']);

        return view('admin.plans.show', compact('plan'));
    }

    public function edit(TreatmentPlan $plan)
    {
        $plan->load(['treatments.service', 'treatments.appointment', 'patient', 'invoices.payments']);
        $services = Service::orderBy('name')->get(['id', 'name', 'price']);

        return view('admin.plans.edit', compact('plan', 'services'));
    }

    /**
     * Actualiza datos del plan.
     * Si la acción es "approve", simplemente marca el plan como aprobado (NO crea citas).
     */
    public function update(Request $request, TreatmentPlan $plan)
    {
        $action = $request->input('action', 'save');

        $data = $request->validate([
            'title'          => ['required', 'string', 'max:120'],
            'status'         => ['required', 'in:draft,approved,in_progress,completed,canceled'],
            'total_sessions' => ['nullable', 'integer', 'min:0'],
        ]);

        $plan->update([
            'title'          => $data['title'],
            'status'         => $data['status'],
            'total_sessions' => $data['total_sessions'] ?? $plan->total_sessions,
        ]);

        // ¿Se quiere aprobar el plan?
        if ($action === 'approve' || $data['status'] === 'approved') {

            if ($plan->treatments->isEmpty()) {
                return back()
                    ->with('error', 'No se puede aprobar un plan sin tratamientos.')
                    ->withInput();
            }

            $plan->update([
                'status'      => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            return back()->with('ok', 'Plan aprobado. Ahora puedes programar citas para cada tratamiento.');
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
     * Recalcula estimate_total y progreso.
     */
    public function recalc(TreatmentPlan $plan)
    {
        $sum = $plan->treatments()->sum('price');
        $completed = $plan->treatments()->where('status', 'done')->count();
        $plan->update([
            'estimate_total'     => $sum,
            'completed_sessions' => $completed,
        ]);

        return back()->with('ok', 'Total y progreso recalculados.');
    }

    public function print(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'treatments.service', 'treatments.appointment', 'approver', 'invoices.payments']);

        return view('admin.plans.print', compact('plan'));
    }

    public function pdf(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'treatments.service', 'treatments.appointment', 'approver', 'invoices.payments']);

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
