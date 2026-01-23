<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determina si el usuario puede ver el listado de pacientes.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'patients.manage',
            'patients.index',
        ]);
    }

    /**
     * Determina si el usuario puede ver un paciente específico.
     */
    public function view(User $user, Patient $patient): bool
    {
        // Staff con permiso puede ver cualquier paciente
        if ($user->hasAnyPermission(['patients.manage', 'patients.show', 'patients.index'])) {
            return true;
        }

        // Paciente solo puede verse a sí mismo
        return optional($user->patient)->id === $patient->id;
    }

    /**
     * Determina si el usuario puede crear pacientes.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['patients.manage', 'patients.create']);
    }

    /**
     * Determina si el usuario puede actualizar un paciente.
     */
    public function update(User $user, Patient $patient): bool
    {
        // Staff con permiso puede editar cualquier paciente
        if ($user->hasAnyPermission(['patients.manage', 'patients.update'])) {
            return true;
        }

        // Paciente puede editar su propio perfil
        return optional($user->patient)->id === $patient->id;
    }

    /**
     * Determina si el usuario puede eliminar un paciente.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasAnyPermission(['patients.manage', 'patients.destroy']);
    }

    /**
     * Determina si el usuario puede ver la historia clínica.
     */
    public function viewHistory(User $user, Patient $patient): bool
    {
        if ($user->hasAnyPermission(['patients.history.view', 'medical_history.manage'])) {
            return true;
        }

        // Paciente puede ver su propia historia
        return optional($user->patient)->id === $patient->id;
    }

    /**
     * Determina si el usuario puede actualizar la historia clínica.
     */
    public function updateHistory(User $user, Patient $patient): bool
    {
        return $user->hasAnyPermission(['patients.history.update', 'medical_history.manage']);
    }
}
