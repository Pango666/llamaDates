<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceToken;

class DeviceTokenController extends Controller
{
    /**
     * POST /api/v1/mobile/device-token
     * Registrar token FCM/APNs
     */
    public function store(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'platform' => 'required|in:android,ios,web',
        ]);

        $user = auth('api')->user();

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id'  => $user->id,
                'platform' => $request->platform
            ]
        );

        return response()->json(['message' => 'Token registrado correctamente']);
    }

    /**
     * DELETE /api/v1/mobile/device-token
     * Eliminar token (Logout)
     */
    public function destroy(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        
        DeviceToken::where('token', $request->token)->delete();

        return response()->json(['message' => 'Token eliminado']);
    }

    /**
     * POST /api/v1/mobile/device-token/test
     * Enviar notificación de prueba al usuario actual
     */
    public function test(Request $request)
    {
        $user = auth('api')->user();

        $push = new \App\Services\PushNotificationService();
        $sent = $push->sendToUser(
            $user->id,
            'Prueba de Notificación',
            '¡Si lees esto, las notificaciones funcionan correctamente! 🚀',
            ['type' => 'test']
        );

        if ($sent) {
            return response()->json(['message' => 'Notificación enviada exitosamente.']);
        } else {
            return response()->json(['error' => 'No se pudo enviar. Verifica que tengas un token registrado.'], 400);
        }
    }
}
