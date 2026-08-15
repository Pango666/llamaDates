<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBotIntegration
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.ceot_bot.key', '');

        if ($configuredKey === '') {
            return new JsonResponse([
                'error' => 'La integracion del chatbot no esta configurada.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $providedKey = (string) $request->header('X-CEOT-Bot-Key', '');

        if ($providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return new JsonResponse([
                'error' => 'Credenciales de integracion invalidas.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
