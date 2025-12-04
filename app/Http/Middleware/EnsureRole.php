<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user() ?? auth('api')->user();

        // No autenticado
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthorized'], 401)
                : redirect()->guest(route('login'))->with('warn', 'Inicia sesión para continuar.');
        }

        // Soporte Spatie (hasRole) y fallback por string
        $allowed = array_map('strval', $roles);
        $hasAllowed = method_exists($user, 'hasRole')
            ? $user->hasRole($allowed)
            : in_array((string)($user->role ?? ''), $allowed, true);

        if (!$hasAllowed) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            // Redirección por rol conocido (SIN "panel")
            return redirect()->route($this->targetRouteFor($user))
                ->with('warn', 'No tienes permisos para esa sección.');
        }

        return $next($request);
    }

    private function targetRouteFor($user): string
    {
        $hasRole = fn ($r) => method_exists($user, 'hasRole')
            ? $user->hasRole($r)
            : (string)($user->role ?? '') === (string)$r;

        if ($hasRole('paciente'))   return 'app.dashboard';
        if ($hasRole('admin'))      return 'admin.dashboard';
        if ($hasRole('asistente'))  return 'admin.appointments.index';
        if ($hasRole('odontologo')) return 'admin.appointments.index';

        // Fallback seguro
        return 'login';
    }
}