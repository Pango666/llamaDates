<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, Invoice $invoice): bool
    {
        $pid = optional($user->patient)->id;
        return $pid && $invoice->patient_id === $pid;
    }
}
