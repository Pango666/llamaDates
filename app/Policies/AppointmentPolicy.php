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

    public function view(User $user, Appointment $appointment): bool
    {
        $pid = optional($user->patient)->id;
        return $pid && $appointment->patient_id === $pid;
    }
}
