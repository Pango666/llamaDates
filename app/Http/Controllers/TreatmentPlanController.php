<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Service;
use App\Models\Treatment;
use App\Models\TreatmentPlan;
use App\Models\Dentist;
use App\Models\Appointment;
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

        // Si se está aprobando el plan, verificar que tenga tratamientos
        if ($data['status'] === 'approved' && $plan->treatments->isEmpty()) {
            return back()->with('error', 'No se puede aprobar un plan sin tratamientos.');
        }

        $plan->update($data);

        // Si se está aprobando el plan, programar las citas
        if ($data['status'] === 'approved' && $plan->wasChanged('status')) {
            $this->scheduleAppointments($plan);
            return back()->with('ok', 'Plan aprobado y citas programadas.');
        }

        return back()->with('ok', 'Plan actualizado.');
    }

    /**
     * Programa las citas para los tratamientos de un plan
     */
    private function scheduleAppointments(TreatmentPlan $plan)
    {
        $patient = $plan->patient;
        $treatments = $plan->treatments()->whereNull('appointment_id')->get();
        
        foreach ($treatments as $treatment) {
            // Buscar el próximo horario disponible para el dentista
            $nextAvailableSlot = $this->findNextAvailableSlot($treatment);
            
            if ($nextAvailableSlot) {
                // Crear la cita
                $appointment = $plan->appointments()->create([
                    'patient_id' => $patient->id,
                    'dentist_id' => $nextAvailableSlot['dentist_id'],
                    'service_id' => $treatment->service_id,
                    'date' => $nextAvailableSlot['date'],
                    'start_time' => $nextAvailableSlot['start_time'],
                    'end_time' => $nextAvailableSlot['end_time'],
                    'status' => 'scheduled',
                    'notes' => 'Plan de tratamiento #' . $plan->id . 
                             ($treatment->tooth_code ? ' · Pieza ' . $treatment->tooth_code : '') .
                             ($treatment->surface ? ' ' . $treatment->surface : ''),
                ]);

                // Asociar la cita al tratamiento
                $treatment->update(['appointment_id' => $appointment->id]);
            }
        }
    }

    /**
     * Encuentra el próximo horario disponible para un tratamiento
     */
    private function findNextAvailableSlot($treatment)
    {
        // Obtener el servicio para la duración
        $service = $treatment->service;
        $duration = $service ? $service->duration_min : 30; // 30 minutos por defecto
        
        // Obtener los dentistas que pueden realizar este servicio
        $dentists = \App\Models\Dentist::whereHas('services', function($q) use ($treatment) {
            $q->where('services.id', $treatment->service_id);
        })->get();

        if ($dentists->isEmpty()) {
            return null;
        }

        // Buscar en los próximos 30 días
        $startDate = now();
        $endDate = now()->addDays(30);
        
        foreach ($dentists as $dentist) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Obtener horarios del dentista para este día de la semana
                $dayOfWeek = $currentDate->dayOfWeek; // 0 (domingo) a 6 (sábado)
                $schedules = $dentist->schedules()->where('day_of_week', $dayOfWeek)->get();
                
                foreach ($schedules as $schedule) {
                    $startTime = \Carbon\Carbon::parse($schedule->start_time);
                    $endTime = \Carbon\Carbon::parse($schedule->end_time);
                    
                    // Verificar disponibilidad en bloques de la duración del servicio
                    $currentSlot = $startTime->copy();
                    
                    while ($currentSlot->copy()->addMinutes($duration)->lte($endTime)) {
                        $slotEnd = $currentSlot->copy()->addMinutes($duration);
                        
                        // Verificar si hay conflicto con otras citas
                        $conflict = \App\Models\Appointment::where('dentist_id', $dentist->id)
                            ->whereDate('date', $currentDate->toDateString())
                            ->where(function($q) use ($currentSlot, $slotEnd) {
                                $q->where(function($q) use ($currentSlot, $slotEnd) {
                                    $q->where('start_time', '<', $slotEnd->format('H:i:s'))
                                      ->where('end_time', '>', $currentSlot->format('H:i:s'));
                                });
                            })
                            ->exists();
                        
                        if (!$conflict) {
                            return [
                                'dentist_id' => $dentist->id,
                                'date' => $currentDate->toDateString(),
                                'start_time' => $currentSlot->format('H:i:s'),
                                'end_time' => $slotEnd->format('H:i:s'),
                            ];
                        }
                        
                        // Mover al siguiente bloque de tiempo (cada 30 minutos)
                        $currentSlot->addMinutes(30);
                    }
                }
                
                $currentDate->addDay();
            }
        }
        
        return null;
    }

    public function destroy(TreatmentPlan $plan)
    {
        $patient = $plan->patient;
        $plan->delete();
        return redirect()->route('admin.patients.plans.index', $patient)->with('ok', 'Plan eliminado.');
    }

    public function approve(Request $request, TreatmentPlan $plan)
    {
        $request->validate([
            'dentist_id' => 'required|exists:dentists,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i'
        ]);

        // Verificar que el plan tenga tratamientos
        if ($plan->treatments->isEmpty()) {
            return back()->with('error', 'No se puede aprobar un plan sin tratamientos.');
        }

        // Verificar disponibilidad del dentista
        $dentist = Dentist::findOrFail($request->dentist_id);
        $appointmentDate = Carbon::parse($request->appointment_date);
        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes(30); // Duración por defecto

        // Verificar si el horario está disponible
        $isAvailable = !Appointment::where('dentist_id', $dentist->id)
            ->whereDate('date', $appointmentDate)
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime->format('H:i:s'))
                      ->where('end_time', '>', $startTime->format('H:i:s'));
                });
            })
            ->exists();

        if (!$isAvailable) {
            return back()->with('error', 'El horario seleccionado ya no está disponible. Por favor, seleccione otro horario.');
        }

        // Actualizar el estado del plan
        $plan->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id()
        ]);

        // Programar citas para cada tratamiento
        foreach ($plan->treatments as $treatment) {
            // Si ya tiene cita, saltar
            if ($treatment->appointment_id) continue;
            
            // Crear la cita
            $appointment = $plan->appointments()->create([
                'patient_id' => $plan->patient_id,
                'dentist_id' => $dentist->id,
                'service_id' => $treatment->service_id,
                'date' => $appointmentDate,
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'status' => 'scheduled',
                'notes' => 'Plan #' . $plan->id . 
                         ($treatment->tooth_code ? ' · Pieza ' . $treatment->tooth_code : '') .
                         ($treatment->surface ? ' ' . $treatment->surface : ''),
            ]);

            // Asociar la cita al tratamiento
            $treatment->update(['appointment_id' => $appointment->id]);

            // Mover al siguiente bloque de tiempo (30 minutos)
            $startTime->addMinutes(30);
            $endTime->addMinutes(30);
        }

        return redirect()->route('admin.plans.edit', $plan)
            ->with('ok', 'Plan aprobado y citas programadas correctamente.');
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
