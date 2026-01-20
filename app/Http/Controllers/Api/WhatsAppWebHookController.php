<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebHookController extends Controller
{
     /**
     * Verificación del webhook (GET).
     * Meta manda hub.mode, hub.verify_token y hub.challenge.
     */
    public function verify(Request $request)
    {
        $verifyToken = env('WHATSAPP_VERIFY_TOKEN', 'llamabot_verify_123');

        $mode      = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token     = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WA_WEBHOOK_VERIFY_OK', [
                'mode'  => $mode,
                'token' => $token,
            ]);

            // Meta espera que le devuelvas el challenge tal cual
            return response($challenge, 200);
        }

        Log::warning('WA_WEBHOOK_VERIFY_FAIL', [
            'mode'      => $mode,
            'token'     => $token,
            'expected'  => $verifyToken,
            'challenge' => $challenge,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Manejo de eventos (POST).
     * Aquí nos llega todo: statuses, errores, mensajes entrantes, etc.
     */
    public function handle(Request $request)
    {
        Log::info('WA_WEBHOOK_EVENT', $request->all());

        // Meta solo necesita 200 OK
        return response()->json(['status' => 'ok'], 200);
    }
}
