<?php

namespace App\Providers;

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
    ];

    public function boot(): void
    {
        // habilita registerPolicies()
        $this->registerPolicies();

        // “admin” todo-poderoso
        Gate::before(function ($user, $ability) {
            // adapta esto a tu implementación de roles.
            // Si usas enum "role" en users:
            if (($user->role ?? null) === 'admin') {
                return true;
            }
            // Si implementaste hasRole():
            // if (method_exists($user, 'hasRole') && $user->hasRole('admin')) return true;

            return null; // no decides -> siguen policies/gates normales
        });
    }
}
