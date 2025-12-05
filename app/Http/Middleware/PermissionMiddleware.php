<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Verifica que el usuario tenga TODOS los permisos indicados.
     * Uso: ->middleware('permission:appointments.manage')
     *      ->middleware('permission:appointments.manage,billing.manage')
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'No autenticado.');
        }

        if (count($permissions) === 1 && str_contains($permissions[0], ',')) {
            $permissions = array_map('trim', explode(',', $permissions[0]));
        }

        foreach ($permissions as $perm) {
            if (!$user->hasPermission($perm)) {
                abort(403, 'No tienes permisos suficientes.');
            }
        }

        return $next($request);
    }
}
