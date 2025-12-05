<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Maneja la verificación de roles.
     *
     * Soporta:
     *  - role:admin
     *  - role:admin|asistente
     *  - role:!paciente          (cualquiera que NO sea paciente)
     *  - fallback a columna 'role' si no usas Spatie
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'No autenticado.');
        }

        // Normalizar roles del middleware: soportar "admin|asistente"
        $normalizedRoles = [];
        foreach ($roles as $r) {
            foreach (explode('|', $r) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $normalizedRoles[] = $part;
                }
            }
        }
        $roles = $normalizedRoles;

        // Si no se pidió ningún rol, dejamos pasar
        if (empty($roles)) {
            return $next($request);
        }

        // Obtener roles del usuario
        $userRoles = [];

        // Si usas Spatie (o similar)
        if (method_exists($user, 'roles')) {
            $userRoles = $user->roles->pluck('name')->all();
        }
        // Fallback por si solo tienes una columna 'role'
        elseif (isset($user->role)) {
            $userRoles = [$user->role];
        }

        // Evaluar condiciones
        $allowed = false;

        foreach ($roles as $role) {
            $negate = false;

            if (is_string($role) && strlen($role) > 0 && $role[0] === '!') {
                $negate = true;
                $role = substr($role, 1);
            }

            if ($negate) {
                // role:!paciente => permitido si NO tiene 'paciente'
                if (!in_array($role, $userRoles, true)) {
                    $allowed = true;
                    break;
                }
            } else {
                // role:admin => permitido si tiene 'admin'
                if (in_array($role, $userRoles, true)) {
                    $allowed = true;
                    break;
                }
            }
        }

        if (!$allowed) {
            abort(403, 'No tienes el rol requerido.');
        }

        return $next($request);
    }
}
