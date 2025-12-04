<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequirePermission
{
    public function handle(Request $request, Closure $next, ...$perms)
    {
        $user = auth()->user() ?? auth('api')->user();
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthorized'], 401)
                : redirect()->guest(route('login'));
        }

        // Adapta esta verificación a tu modelo User
        // Por ejemplo si guardas permisos en una relación o json:
        $has = function(string $p) use ($user) {
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($p);
            }
            // fallback simple: asume columna json 'permissions' en users
            $arr = (array) ($user->permissions ?? []);
            return in_array($p, $arr, true);
        };

        foreach ($perms as $p) {
            if (!$has($p)) {
                return $request->expectsJson()
                    ? response()->json(['message' => 'Forbidden'], 403)
                    : abort(403, 'Forbidden');
            }
        }

        return $next($request);
    }
}