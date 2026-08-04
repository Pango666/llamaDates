<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function __construct()
    {
        //
    }

    /**
     * Determina si el usuario puede ver el listado de citas.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'appointments.manage',
            'appointments.index',
            'agenda.view',
        ]);
    }

    /**
     * Determina si el usuario puede ver una cita específica.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasPermission('appointments.manage')) {
            return true;
        }

        if ($user->hasAnyPermission(['appointments.show', 'agenda.view'])) {
            $dentistId = optional($user->dentist)->id;

            return ! $dentistId || $appointment->dentist_id === $dentistId;
        }

        // Paciente solo puede ver sus propias citas
        $pid = optional($user->patient)->id;

        return $pid && $appointment->patient_id === $pid;
    }

    /**
     * Determina si el usuario puede crear citas.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'appointments.manage',
            'appointments.create',
            'patient.appointments.create',
        ]);
    }

    /**
     * Determina si el usuario puede actualizar una cita.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->hasPermission('appointments.manage')) {
            return true;
        }

        if (! $user->hasPermission('appointments.update_status')) {
            return false;
        }

        $dentistId = optional($user->dentist)->id;

        return ! $dentistId || $appointment->dentist_id === $dentistId;
    }

    /**
     * Determina si el usuario puede cancelar una cita.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        // Staff puede cancelar cualquier cita
        if ($user->hasAnyPermission(['appointments.manage', 'appointments.cancel'])) {
            return true;
        }

        // Paciente solo puede cancelar sus propias citas
        $pid = optional($user->patient)->id;

        return $pid && $appointment->patient_id === $pid;
    }

    /**
     * Determina si el usuario puede eliminar una cita.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasPermission('appointments.manage');
    }

    /**
     * Determina si el usuario puede confirmar una cita.
     */
    public function confirm(User $user, Appointment $appointment): bool
    {
        // Staff puede confirmar cualquier cita
        if ($user->hasAnyPermission(['appointments.manage', 'appointments.update_status'])) {
            return true;
        }

        // Paciente solo puede confirmar sus propias citas
        $pid = optional($user->patient)->id;

        return $pid && $appointment->patient_id === $pid;
    }
}
