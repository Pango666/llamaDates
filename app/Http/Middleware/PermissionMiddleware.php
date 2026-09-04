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

        foreach ($permissions as $permGroup) {
            if (str_contains($permGroup, '|')) {
                $subPerms = array_map('trim', explode('|', $permGroup));
                $hasAny = false;
                foreach ($subPerms as $sp) {
                    if ($user->hasPermission($sp)) {
                        $hasAny = true;
                        break;
                    }
                }
                if (!$hasAny) {
                    abort(403, 'No tienes permisos suficientes.');
                }
            } else {
                if (!$user->hasPermission($permGroup)) {
                    abort(403, 'No tienes permisos suficientes.');
                }
            }
        }

        return $next($request);
    }
}
