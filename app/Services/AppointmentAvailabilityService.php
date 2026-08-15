<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Schedule;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentAvailabilityService
{
    /**
     * @return array<int, string>
     */
    public function slots(
        Dentist $dentist,
        Service $service,
        string $date,
        ?int $excludeAppointmentId = null
    ): array {
        return array_keys($this->slotMap($dentist, $service, $date, $excludeAppointmentId));
    }

    /**
     * @return array{start: Carbon, end: Carbon, chair_id: int}|null
     */
    public function resolveSlot(
        Dentist $dentist,
        Service $service,
        string $date,
        string $time,
        ?int $excludeAppointmentId = null
    ): ?array {
        $normalizedTime = Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('H:i');

        return $this->slotMap($dentist, $service, $date, $excludeAppointmentId)[$normalizedTime] ?? null;
    }

    public function patientHasConflict(
        int $patientId,
        Carbon $start,
        Carbon $end,
        ?int $excludeAppointmentId = null
    ): bool {
        return $this->activeAppointments($start->toDateString(), $excludeAppointmentId)
            ->where('patient_id', $patientId)
            ->contains(fn (Appointment $appointment) => $this->overlaps($appointment, $start, $end));
    }

    /**
     * @return array<string, array{start: Carbon, end: Carbon, chair_id: int}>
     */
    private function slotMap(
        Dentist $dentist,
        Service $service,
        string $date,
        ?int $excludeAppointmentId
    ): array {
        $timezone = config('app.timezone', 'America/La_Paz');
        $day = Carbon::parse($date, $timezone)->startOfDay();
        $now = now($timezone);

        if ($day->lt($now->copy()->startOfDay()) || ! $dentist->status || ! $service->active) {
            return [];
        }

        $duration = max(1, (int) ($service->duration_min ?: 30));
        $schedules = Schedule::where('dentist_id', $dentist->id)
            ->where('day_of_week', $day->dayOfWeek)
            ->orderBy('start_time')
            ->get();

        $appointments = $this->activeAppointments($day->toDateString(), $excludeAppointmentId);
        $slots = [];

        foreach ($schedules as $schedule) {
            $chairId = (int) ($schedule->chair_id ?: $dentist->chair_id);
            if ($chairId <= 0) {
                continue;
            }

            $scheduleStart = Carbon::parse($day->toDateString().' '.$schedule->start_time, $timezone);
            $scheduleEnd = Carbon::parse($day->toDateString().' '.$schedule->end_time, $timezone);
            $breaks = collect($schedule->breaks ?? [])->map(fn (array $break) => [
                'start' => Carbon::parse($day->toDateString().' '.$break['start'], $timezone),
                'end' => Carbon::parse($day->toDateString().' '.$break['end'], $timezone),
            ]);

            for ($cursor = $scheduleStart->copy(); $cursor->copy()->addMinutes($duration)->lte($scheduleEnd); $cursor->addMinutes($duration)) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addMinutes($duration);

                if ($day->isSameDay($now) && $slotStart->lte($now)) {
                    continue;
                }

                if ($breaks->contains(fn (array $break) => $slotStart->lt($break['end']) && $slotEnd->gt($break['start']))) {
                    continue;
                }

                $hasDentistConflict = $appointments
                    ->where('dentist_id', $dentist->id)
                    ->contains(fn (Appointment $appointment) => $this->overlaps($appointment, $slotStart, $slotEnd));

                $hasChairConflict = $appointments
                    ->where('chair_id', $chairId)
                    ->contains(fn (Appointment $appointment) => $this->overlaps($appointment, $slotStart, $slotEnd));

                if (! $hasDentistConflict && ! $hasChairConflict) {
                    $slots[$slotStart->format('H:i')] = [
                        'start' => $slotStart,
                        'end' => $slotEnd,
                        'chair_id' => $chairId,
                    ];
                }
            }
        }

        ksort($slots);

        return $slots;
    }

    /**
     * @return Collection<int, Appointment>
     */
    private function activeAppointments(string $date, ?int $excludeAppointmentId): Collection
    {
        return Appointment::whereDate('date', $date)
            ->where('is_active', true)
            ->where('status', '!=', 'canceled')
            ->when($excludeAppointmentId, fn ($query) => $query->whereKeyNot($excludeAppointmentId))
            ->get();
    }

    private function overlaps(Appointment $appointment, Carbon $start, Carbon $end): bool
    {
        $timezone = config('app.timezone', 'America/La_Paz');
        $appointmentStart = Carbon::parse($start->toDateString().' '.$appointment->start_time, $timezone);
        $appointmentEnd = Carbon::parse($start->toDateString().' '.$appointment->end_time, $timezone);

        return $start->lt($appointmentEnd) && $end->gt($appointmentStart);
    }
}
