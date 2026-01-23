<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Si tienes Policies, mapéalas aquí:
     * protected $policies = [
     *     \App\Models\Algo::class => \App\Policies\AlgoPolicy::class,
     * ];
     */
    protected $policies = [
        // \App\Models\Model::class => \App\Policies\ModelPolicy::class,
        \App\Models\Appointment::class => \App\Policies\AppointmentPolicy::class,
        \App\Models\Invoice::class     => \App\Policies\InvoicePolicy::class,
        \App\Models\Consent::class     => \App\Policies\ConsentPolicy::class,
        \App\Models\Patient::class     => \App\Policies\PatientPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Admin full-access opcional
        Gate::before(function (User $user, string $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true; // admin puede todo
            }

            // Si el usuario tiene un método hasPermission, úsalo
            if (method_exists($user, 'hasPermission')) {
                // IMPORTANTE: devolver true o null, NO false
                return $user->hasPermission($ability) ?: null;
            }

            return null;
        });
    }
}
