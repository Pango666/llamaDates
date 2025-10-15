<?php

namespace App\Policies;

use App\Models\Consent;
use App\Models\User;

class ConsentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, Consent $consent): bool
    {
        $pid = optional($user->patient)->id;
        return $pid && $consent->patient_id === $pid;
    }
}
