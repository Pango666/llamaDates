<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    /**
     * GET /api/v1/mobile/appointments
     * Filtros: ?status=upcoming (default) | history
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();
        
        if (!$patient) return response()->json(['error' => 'Paciente no encontrado'], 404);

        $query = Appointment::with(['service:id,name,price', 'dentist:id,name'])
                    ->where('patient_id', $patient->id);

        $mode = $request->get('status', 'upcoming');

        if ($mode === 'upcoming') {
            $query->where(function($q) {
                $q->where('date', '>', now()->toDateString())
                  ->orWhere(function($sub) {
                      $sub->where('date', now()->toDateString())
                          ->where('start_time', '>=', now()->format('H:i:s'));
                  });
            })->whereIn('status', ['reserved', 'confirmed']);
            
            $query->orderBy('date', 'asc')->orderBy('start_time', 'asc');

        } elseif ($mode === 'history') {
            $query->where('date', '<', now()->toDateString()) // Pasadas
                  ->orWhereIn('status', ['done', 'completed', 'canceled', 'no_show']);
            
            $query->orderBy('date', 'desc')->orderBy('start_time', 'desc');
        }

        $appointments = $query->paginate(20);

        return response()->json($appointments);
    }

    /**
     * GET /api/v1/mobile/appointments/{id}
     */
    public function show($id)
    {
        $user = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();

        $appointment = Appointment::with(['service', 'dentist', 'chair'])
                        ->where('patient_id', $patient->id)
                        ->where('id', $id)
                        ->firstOrFail();

        return response()->json($appointment);
    }

    /**
     * POST /api/v1/mobile/appointments
     * Reservar Cita
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();
        
        if (!$patient) {
            return response()->json(['error' => 'Tu usuario no tiene un perfil de paciente asociado.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'dentist_id' => 'required|exists:dentists,id',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date|after_or_equal:today',
            'time'       => 'required|date_format:H:i',
            'notes'      => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Datos inválidos', 'details' => $validator->errors()], 422);
        }

        $service = Service::find($request->service_id);
        $dentist = Dentist::find($request->dentist_id);

        // Calcular hora fin
        $startTime = $request->time;
        // Normalizar H:i -> H:i:s
        if(strlen($startTime) == 5) $startTime .= ":00";
        
        $duration = $service->duration_min ?? 30;
        $startCarbon = Carbon::parse($request->date . ' ' . $startTime);
        $endCarbon   = $startCarbon->copy()->addMinutes($duration);
        
        if ($startCarbon->isPast()) {
            return response()->json(['error' => 'No puedes reservar en el pasado.'], 422);
        }

        // Validar conflicto
        $conflict = Appointment::where('dentist_id', $dentist->id)
            ->whereDate('date', $request->date)
            ->where('is_active', true)
            ->where(function($q) use ($startTime, $endCarbon) {
                 $endTimeStr = $endCarbon->format('H:i:s');
                 // Overlap logic
                 $q->where('start_time', '<', $endTimeStr)
                   ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($conflict) {
             return response()->json(['error' => 'El horario seleccionado ya está ocupado.'], 422);
        }

        // Crear Cita
        try {
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'service_id' => $service->id,
                'chair_id'   => $dentist->chair_id, // Asume silla del dentista
                'date'       => $request->date,
                'start_time' => $startTime,
                'end_time'   => $endCarbon->format('H:i:s'),
                'status'     => 'reserved',
                'is_active'  => true,
                'notes'      => $request->notes . ' (Desde App Móvil)',
            ]);

            // Enviar email confirmación (Try Catch interno)
            try {
                if ($patient->email) {
                    \Illuminate\Support\Facades\Mail::to($patient->email)
                        ->send(new \App\Mail\AppointmentConfirmation($appointment));
                }
            } catch (\Exception $e) {
                Log::error("Mail error mobile app: ".$e->getMessage());
            }

            return response()->json([
                'message' => 'Cita reservada exitosamente.',
                'appointment' => $appointment
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error servidor: '.$e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/mobile/appointments/{id}/cancel
     */
    public function cancel($id, Request $request)
    {
        $user = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();

        $appointment = Appointment::where('id', $id)
                        ->where('patient_id', $patient->id)
                        ->first();

        if (!$appointment) {
            return response()->json(['error' => 'Cita no encontrada o no te pertenece.'], 404);
        }

        if (!in_array($appointment->status, ['reserved', 'confirmed'])) {
             return response()->json(['error' => 'La cita no se puede cancelar en este estado.'], 422);
        }

        // Validar tiempo (no cancelar pasado)
        $startStr = $appointment->date->format('Y-m-d') . ' ' . $appointment->start_time;
        if (Carbon::parse($startStr)->isPast()) {
             return response()->json(['error' => 'No puedes cancelar una cita que ya pasó.'], 422);
        }

        $appointment->update([
            'status' => 'canceled',
            'is_active' => false,
            'canceled_reason' => $request->input('reason', 'Cancelado desde App')
        ]);

        return response()->json(['message' => 'Cita cancelada correctamente.']);
    }

    /**
     * GET /api/v1/mobile/slots
     * Params: date (Y-m-d), dentist_id, service_id
     */
    public function slots(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'date'       => 'required|date|after_or_equal:today',
            'dentist_id' => 'required|exists:dentists,id',
            'service_id' => 'required|exists:services,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Datos inválidos', 'details' => $validator->errors()], 422);
        }

        // --- Reusing Logic from BotController/AppointmentController ---
        $date = $request->date;
        $dentistId = $request->dentist_id;
        $serviceId = $request->service_id;
        
        $service  = Service::find($serviceId);
        $duration = $service->duration_min ?? 30;

        $dayOfWeek = Carbon::parse($date)->dayOfWeek; // 0=Sun, 6=Sat
        
        $schedule = Schedule::where('dentist_id', $dentistId)
                            ->where('day_of_week', $dayOfWeek) 
                            ->first();

        if (!$schedule) {
             return response()->json(['slots' => [], 'message' => 'El odontólogo no trabaja este día.']);
        }

        $startWork = Carbon::parse($date . ' ' . $schedule->start_time);
        $endWork   = Carbon::parse($date . ' ' . $schedule->end_time);

        // Fetch existing appointments
        $appointments = Appointment::where('dentist_id', $dentistId)
                                   ->where('date', $date)
                                   ->whereIn('status', ['confirmed', 'reserved'])
                                   ->where('is_active', true)
                                   ->get();

        $slots = [];
        $current = $startWork->copy();

        // If today, filter out passed time
        $now = now();
        if ($date === $now->toDateString()) {
             if ($current->lt($now)) {
                 $current = $now->copy()->addMinutes(15 - ($now->minute % 15)); // Next 15m block
             }
        }

        while ($current->copy()->addMinutes($duration)->lte($endWork)) {
            $slotStart = $current->copy();
            $slotEnd   = $current->copy()->addMinutes($duration);
            
            $isFree = true;
            foreach ($appointments as $appt) {
                // Ensure proper parsing
                $apptStart = Carbon::parse($date . ' ' . $appt->start_time);
                $apptEnd   = Carbon::parse($date . ' ' . $appt->end_time);

                // Check overlap
                if ($slotStart->lt($apptEnd) && $slotEnd->gt($apptStart)) {
                    $isFree = false;
                    break;
                }
            }

            if ($isFree) {
                $slots[] = $slotStart->format('H:i');
            }

            $current->addMinutes($duration); // Step by duration (or fixed 15/30m?)
            // Usually step by duration or 15min. Let's use duration for simplicity as per Bot logic.
            // But if duration is long (60m), finding slots might be harder if we jump 60m. 
            // Better to jump 15m or 30m? Bot uses duration jump. Keeping consistent.
        }

        return response()->json(['slots' => $slots]);
    }
}
