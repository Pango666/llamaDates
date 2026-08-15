<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use App\Services\AppointmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BotAppointmentController extends Controller
{
    public function __construct(private readonly AppointmentAvailabilityService $availability) {}

    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'dentist_id' => [
                'required',
                Rule::exists('dentists', 'id')->where(fn ($query) => $query->where('status', true)),
            ],
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(fn ($query) => $query->where('active', true)),
            ],
        ]);

        $dentist = Dentist::with('user')->findOrFail($data['dentist_id']);
        $service = Service::findOrFail($data['service_id']);
        $this->ensureDentistIsActive($dentist);

        return response()->json([
            'slots' => $this->availability->slots($dentist, $service, $data['date']),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_identifier' => ['required', 'string', 'max:50'],
            'include_history' => ['sometimes', 'boolean'],
        ]);

        $patient = $this->findPatient($data['patient_identifier']);

        if (! $patient) {
            return response()->json([
                'message' => 'No encontramos un paciente con ese dato.',
                'appointments' => [],
            ]);
        }

        $includeHistory = (bool) ($data['include_history'] ?? false);
        $query = Appointment::with(['dentist:id,name,specialty', 'service:id,name'])
            ->where('patient_id', $patient->id);

        if ($includeHistory) {
            $query->orderByDesc('date')
                ->orderByDesc('start_time')
                ->limit(50);
        } else {
            $query->whereDate('date', '>=', now()->toDateString())
                ->whereIn('status', ['reserved', 'confirmed'])
                ->where('is_active', true)
                ->orderBy('date')
                ->orderBy('start_time');
        }

        $appointments = $query->get()
            ->map(fn (Appointment $appointment) => $this->appointmentPayload($appointment));

        return response()->json([
            'message' => $appointments->isEmpty()
                ? ($includeHistory ? 'No tienes citas registradas.' : 'No tienes citas futuras programadas.')
                : ($includeHistory ? 'Este es tu historial reciente de citas.' : 'Estas son tus proximas citas.'),
            'appointments' => $appointments,
        ]);
    }

    public function book(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_identifier' => ['required', 'string', 'max:50'],
            'dentist_id' => [
                'required',
                Rule::exists('dentists', 'id')->where(fn ($query) => $query->where('status', true)),
            ],
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(fn ($query) => $query->where('active', true)),
            ],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        $patient = $this->findPatient($data['patient_identifier']);
        if (! $patient || ! $patient->is_active) {
            throw ValidationException::withMessages([
                'patient_identifier' => 'El paciente no existe o esta inactivo.',
            ]);
        }

        $appointment = DB::transaction(function () use ($data, $patient) {
            Appointment::whereDate('date', $data['date'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->get();

            $startTime = Carbon::createFromFormat('H:i', $data['time'])->format('H:i:s');
            $existing = Appointment::where('patient_id', $patient->id)
                ->where('dentist_id', $data['dentist_id'])
                ->where('service_id', $data['service_id'])
                ->whereDate('date', $data['date'])
                ->where('start_time', $startTime)
                ->where('is_active', true)
                ->whereIn('status', ['reserved', 'confirmed'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $dentist = Dentist::with('user')->findOrFail($data['dentist_id']);
            $service = Service::findOrFail($data['service_id']);
            $this->ensureDentistIsActive($dentist);

            $slot = $this->availability->resolveSlot(
                $dentist,
                $service,
                $data['date'],
                $data['time']
            );

            if (! $slot) {
                throw ValidationException::withMessages([
                    'time' => 'El horario ya no esta disponible o no pertenece al turno del odontologo.',
                ]);
            }

            if ($this->availability->patientHasConflict($patient->id, $slot['start'], $slot['end'])) {
                throw ValidationException::withMessages([
                    'time' => 'El paciente ya tiene otra cita en ese horario.',
                ]);
            }

            return Appointment::create([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'service_id' => $service->id,
                'chair_id' => $slot['chair_id'],
                'date' => $data['date'],
                'start_time' => $slot['start']->format('H:i:s'),
                'end_time' => $slot['end']->format('H:i:s'),
                'status' => 'reserved',
                'is_active' => true,
                'notes' => 'Reservado mediante WhatsApp',
            ]);
        }, 3);

        $appointment->load(['dentist:id,name,specialty', 'service:id,name']);

        return response()->json([
            'message' => 'Cita reservada. Solicita confirmacion al paciente antes de confirmarla.',
            'appointment' => $this->appointmentPayload($appointment),
        ], $appointment->wasRecentlyCreated ? 201 : 200);
    }

    public function confirm(Request $request, Appointment $appointment): JsonResponse
    {
        $data = $request->validate([
            'patient_identifier' => ['required', 'string', 'max:50'],
        ]);

        $patient = $this->requireOwner($appointment, $data['patient_identifier']);
        $shouldNotify = false;

        $appointment = DB::transaction(function () use ($appointment, $patient, &$shouldNotify) {
            $locked = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $this->ensureOwnership($locked, $patient);

            if ($locked->status === 'confirmed') {
                return $locked;
            }

            if ($locked->status !== 'reserved' || ! $locked->is_active) {
                throw ValidationException::withMessages([
                    'appointment' => 'La cita no puede confirmarse en su estado actual.',
                ]);
            }

            if ($locked->start_at->isPast()) {
                throw ValidationException::withMessages([
                    'appointment' => 'No se puede confirmar una cita pasada.',
                ]);
            }

            $locked->update(['status' => 'confirmed', 'is_active' => true]);
            $shouldNotify = true;

            return $locked->fresh();
        });

        try {
            if ($shouldNotify && $appointment->patient?->email) {
                Mail::to($appointment->patient->email)->send(new AppointmentConfirmation($appointment));
            }
        } catch (\Throwable $exception) {
            Log::error('[Bot] No se pudo enviar la confirmacion de cita.', [
                'appointment_id' => $appointment->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        $appointment->load(['dentist:id,name,specialty', 'service:id,name']);

        return response()->json([
            'message' => 'Cita confirmada correctamente.',
            'appointment' => $this->appointmentPayload($appointment),
        ]);
    }

    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $data = $request->validate([
            'patient_identifier' => ['required', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $patient = $this->requireOwner($appointment, $data['patient_identifier']);

        $appointment = DB::transaction(function () use ($appointment, $patient, $data) {
            $locked = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $this->ensureOwnership($locked, $patient);

            if ($locked->status === 'canceled') {
                return $locked;
            }

            if (! in_array($locked->status, ['reserved', 'confirmed'], true)) {
                throw ValidationException::withMessages([
                    'appointment' => 'La cita no puede cancelarse en su estado actual.',
                ]);
            }

            if ($locked->start_at->isPast()) {
                throw ValidationException::withMessages([
                    'appointment' => 'No se puede cancelar una cita pasada.',
                ]);
            }

            $locked->update([
                'status' => 'canceled',
                'is_active' => false,
                'canceled_at' => now(),
                'canceled_by' => null,
                'canceled_reason' => $data['reason'] ?? 'Cancelado mediante WhatsApp',
            ]);

            return $locked->fresh();
        });

        $appointment->load(['dentist:id,name,specialty', 'service:id,name']);

        return response()->json([
            'message' => 'Cita cancelada correctamente.',
            'appointment' => $this->appointmentPayload($appointment),
        ]);
    }

    public function reschedule(Request $request, Appointment $appointment): JsonResponse
    {
        $data = $request->validate([
            'patient_identifier' => ['required', 'string', 'max:50'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        $patient = $this->requireOwner($appointment, $data['patient_identifier']);

        $appointment = DB::transaction(function () use ($appointment, $patient, $data) {
            Appointment::whereDate('date', $data['date'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->get();

            $locked = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $this->ensureOwnership($locked, $patient);

            if (! in_array($locked->status, ['reserved', 'confirmed'], true) || ! $locked->is_active) {
                throw ValidationException::withMessages([
                    'appointment' => 'La cita no puede reprogramarse en su estado actual.',
                ]);
            }

            if (
                $locked->date->toDateString() === $data['date']
                && substr($locked->start_time, 0, 5) === $data['time']
            ) {
                return $locked;
            }

            $dentist = Dentist::with('user')->findOrFail($locked->dentist_id);
            $service = Service::findOrFail($locked->service_id);
            $this->ensureDentistIsActive($dentist);

            $slot = $this->availability->resolveSlot(
                $dentist,
                $service,
                $data['date'],
                $data['time'],
                $locked->id
            );

            if (! $slot) {
                throw ValidationException::withMessages([
                    'time' => 'El nuevo horario ya no esta disponible o no pertenece al turno del odontologo.',
                ]);
            }

            if ($this->availability->patientHasConflict($patient->id, $slot['start'], $slot['end'], $locked->id)) {
                throw ValidationException::withMessages([
                    'time' => 'El paciente ya tiene otra cita en ese horario.',
                ]);
            }

            $locked->update([
                'date' => $data['date'],
                'start_time' => $slot['start']->format('H:i:s'),
                'end_time' => $slot['end']->format('H:i:s'),
                'chair_id' => $slot['chair_id'],
                'status' => 'reserved',
                'is_active' => true,
            ]);

            return $locked->fresh();
        }, 3);

        $appointment->load(['dentist:id,name,specialty', 'service:id,name']);

        return response()->json([
            'message' => 'Cita reprogramada. Debe confirmarse nuevamente.',
            'appointment' => $this->appointmentPayload($appointment),
        ]);
    }

    private function requireOwner(Appointment $appointment, string $identifier): Patient
    {
        $patient = $this->findPatient($identifier);

        if (! $patient) {
            abort(404, 'Cita no encontrada.');
        }

        $this->ensureOwnership($appointment, $patient);

        return $patient;
    }

    private function ensureOwnership(Appointment $appointment, Patient $patient): void
    {
        if ((int) $appointment->patient_id !== (int) $patient->id) {
            abort(404, 'Cita no encontrada.');
        }
    }

    private function findPatient(string $identifier): ?Patient
    {
        $identifier = trim($identifier);
        $digits = preg_replace('/\D+/', '', $identifier) ?: '';
        $phones = array_filter([$identifier, $digits]);

        if (str_starts_with($digits, '591') && strlen($digits) > 3) {
            $phones[] = substr($digits, 3);
        }

        return Patient::where('ci', $identifier)
            ->orWhereIn('phone', array_values(array_unique($phones)))
            ->first();
    }

    private function ensureDentistIsActive(Dentist $dentist): void
    {
        if (! $dentist->status || ($dentist->user_id && $dentist->user->status !== 'active')) {
            throw ValidationException::withMessages([
                'dentist_id' => 'El odontologo seleccionado no esta activo.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function appointmentPayload(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'date' => $appointment->date->toDateString(),
            'time' => substr($appointment->start_time, 0, 5),
            'status' => $appointment->status,
            'dentist' => [
                'id' => $appointment->dentist_id,
                'name' => $appointment->dentist?->name,
                'specialty' => $appointment->dentist?->specialty,
            ],
            'service' => [
                'id' => $appointment->service_id,
                'name' => $appointment->service?->name,
            ],
        ];
    }
}
