<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Service;
use App\Models\Treatment;
use App\Models\TreatmentPlan;
use App\Models\Dentist;
use App\Models\Appointment;
use App\Models\Schedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreatmentPlanController extends Controller
{
    // Lista de planes por paciente
    public function index(Patient $patient)
    {
        $plans = TreatmentPlan::where('patient_id', $patient->id)
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
            'title' => ['required', 'string', 'max:120'],
        ]);

        $plan = TreatmentPlan::create([
            'patient_id'     => $patient->id,
            'title'          => $data['title'],
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
        $plan->load(['treatments.service']);
        $services = Service::orderBy('name')->get(['id', 'name', 'price']);

        return view('admin.plans.edit', compact('plan', 'services'));
    }

    /**
     * Actualiza datos del plan.
     *
     * Si la acción es "approve" o el status nuevo es "approved",
     * se intenta aprobar el plan y crear las citas usando los slots planificados
     * en cada tratamiento (dentist_id + planned_date + planned_start_time).
     */
    public function update(Request $request, TreatmentPlan $plan)
    {
        $action = $request->input('action', 'save');

        $data = $request->validate([
            'title'  => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:draft,approved,in_progress,closed'],
        ]);

        // Siempre actualizamos título y estado enviado
        $plan->update([
            'title'  => $data['title'],
            'status' => $data['status'],
        ]);

        // ¿Se quiere aprobar el plan explícitamente?
        if ($action === 'approve' || $data['status'] === 'approved') {

            if ($plan->treatments->isEmpty()) {
                return back()
                    ->with('error', 'No se puede aprobar un plan sin tratamientos.')
                    ->withInput();
            }

            // Intentar aprobar el plan y crear citas reales
            $result = $this->approveUsingPlannedSlots($plan);

            if ($result['ok']) {
                return back()->with('ok', 'Plan aprobado y citas programadas.');
            }

            // Si hubo errores al crear las citas, se revierte el estado del plan a "draft"
            $plan->update(['status' => 'draft']);

            return back()
                ->with('error', "No se pudo aprobar el plan:\n" . implode("\n", $result['errors']))
                ->withInput();
        }

        return back()->with('ok', 'Plan actualizado.');
    }

    /**
     * Método legacy de destroy (lo mantenemos igual)
     */
    public function destroy(TreatmentPlan $plan)
    {
        $patient = $plan->patient;
        $plan->delete();

        return redirect()
            ->route('admin.patients.plans.index', $patient)
            ->with('ok', 'Plan eliminado.');
    }

    /**
     * Marca el plan "en ejecución" (no crea ni toca citas).
     */
    public function start(TreatmentPlan $plan)
    {
        $plan->update(['status' => 'in_progress']);

        return back()->with('ok', 'Plan en ejecución.');
    }

    /**
     * Recalcula estimate_total = suma de precios de los tratamientos.
     */
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

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('admin.plans.print', ['plan' => $plan]);
            $filename = 'plan_' . $plan->id . '.pdf';

            return $pdf->download($filename);
        }

        return redirect()
            ->route('admin.plans.print', $plan)
            ->with('warn', 'Para PDF instala barryvdh/laravel-dompdf. Te abrí la vista imprimible.');
    }

    /**
     * APRUEBA el plan usando los horarios planificados en cada tratamiento
     * y crea citas reales respetando el flujo de AppointmentController::store.
     *
     * - Usa service.duration_min para calcular end_time.
     * - Respeta Schedule (bloques de turno) y chair_id.
     * - Verifica solapamiento con otras citas.
     *
     * @return array ['ok' => bool, 'errors' => array]
     */
    private function approveUsingPlannedSlots(TreatmentPlan $plan): array
    {
        $errors = [];
        $tz     = config('app.timezone', 'America/La_Paz');

        // 1) Validar TODOS los tratamientos antes de crear nada
        foreach ($plan->treatments as $idx => $treatment) {
            /** @var Treatment $treatment */
            $service = $treatment->service;

            if (!$service) {
                $errors[] = "Tratamiento #" . ($idx + 1) . " sin servicio válido.";
                continue;
            }

            // Debe tener doctor + fecha + hora de inicio planificados
            if (!$treatment->dentist_id || !$treatment->planned_date || !$treatment->planned_start_time) {
                $errors[] = "Tratamiento '{$service->name}' no tiene doctor, fecha u hora planificada.";
                continue;
            }

            $duration = (int) ($service->duration_min ?? 30);
            if ($duration <= 0) {
                $duration = 30;
            }

            $date  = Carbon::parse($treatment->planned_date, $tz);
            $start = Carbon::parse($treatment->planned_date . ' ' . $treatment->planned_start_time, $tz);
            $end   = $start->copy()->addMinutes($duration);

            if ($start->isPast()) {
                $errors[] = "Tratamiento '{$service->name}' tiene un horario en el pasado.";
                continue;
            }

            // Conflicto con otras citas activas
            $conflict = Appointment::where('dentist_id', $treatment->dentist_id)
                ->whereDate('date', $date->toDateString())
                ->where('is_active', true)
                ->where('start_time', '<', $end->format('H:i:s'))
                ->where('end_time', '>', $start->format('H:i:s'))
                ->exists();

            if ($conflict) {
                $errors[] = "Horario no disponible para '{$service->name}' el "
                    . $date->format('Y-m-d') . ' a las ' . $start->format('H:i');
                continue;
            }

            // Debe pertenecer a algún bloque de horario (Schedule) del odontólogo
            $dow = $date->dayOfWeek; // 0..6
            $block = Schedule::where('dentist_id', $treatment->dentist_id)
                ->where('day_of_week', $dow)
                ->where('start_time', '<=', $start->format('H:i:s'))
                ->where('end_time', '>=', $end->format('H:i:s'))
                ->orderBy('start_time', 'desc')
                ->first();

            if (!$block) {
                $errors[] = "El horario seleccionado para '{$service->name}' no pertenece al turno del odontólogo.";
                continue;
            }

            $chairId = $block->chair_id ?? Dentist::whereKey($treatment->dentist_id)->value('chair_id');

            if (!$chairId) {
                $errors[] = "No hay silla asignada para '{$service->name}' en esa franja.";
                continue;
            }
        }

        if (!empty($errors)) {
            return ['ok' => false, 'errors' => $errors];
        }

        // 2) Si todo validó, crear citas reales – transacción para seguridad
        DB::transaction(function () use ($plan, $tz) {
            foreach ($plan->treatments as $treatment) {
                /** @var Treatment $treatment */
                $service = $treatment->service;

                if (!$service || !$treatment->dentist_id || !$treatment->planned_date || !$treatment->planned_start_time) {
                    continue; // No debería ocurrir si ya validamos, pero por si acaso
                }

                $duration = (int) ($service->duration_min ?? 30);
                if ($duration <= 0) {
                    $duration = 30;
                }

                $date  = Carbon::parse($treatment->planned_date, $tz);
                $start = Carbon::parse($treatment->planned_date . ' ' . $treatment->planned_start_time, $tz);
                $end   = $start->copy()->addMinutes($duration);

                // Bloque y silla (ya sabemos que existen, pero los buscamos de nuevo)
                $dow = $date->dayOfWeek;
                $block = Schedule::where('dentist_id', $treatment->dentist_id)
                    ->where('day_of_week', $dow)
                    ->where('start_time', '<=', $start->format('H:i:s'))
                    ->where('end_time', '>=', $end->format('H:i:s'))
                    ->orderBy('start_time', 'desc')
                    ->first();

                $chairId = $block->chair_id ?? Dentist::whereKey($treatment->dentist_id)->value('chair_id');

                $appointment = Appointment::create([
                    'patient_id' => $plan->patient_id,
                    'dentist_id' => $treatment->dentist_id,
                    'service_id' => $treatment->service_id,
                    'chair_id'   => $chairId,
                    'date'       => $date->toDateString(),
                    'start_time' => $start->format('H:i:s'),
                    'end_time'   => $end->format('H:i:s'),
                    'status'     => 'reserved',
                    'is_active'  => true,
                    'notes'      => 'Plan #' . $plan->id
                        . ($treatment->tooth_code ? ' · Pieza ' . $treatment->tooth_code : '')
                        . ($treatment->surface ? ' ' . $treatment->surface : ''),
                ]);

                $treatment->update(['appointment_id' => $appointment->id]);
            }

            // Finalmente marcamos el plan como aprobado
            $plan->update([
                'status'      => 'approved',
                'approved_at' => now($tz),
                'approved_by' => Auth::id(),
            ]);
        });

        return ['ok' => true, 'errors' => []];
    }
}
