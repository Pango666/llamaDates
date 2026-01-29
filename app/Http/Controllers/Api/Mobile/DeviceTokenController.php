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

        // 1. Diagnóstico de Tokens
        $count = DeviceToken::where('user_id', $user->id)->count();
        if ($count === 0) {
            return response()->json([
                'error' => 'No hay tokens registrados para este usuario.',
                'debug' => [
                    'user_id' => $user->id, 
                    'user_name' => $user->name,
                    'db_count' => 0
                ]
            ], 400);
        }

        // 2. Diagnóstico de Archivo
        $path = storage_path('app/firebase.json');
        if (!file_exists($path)) {
            return response()->json([
                'error' => 'No se encuentra el archivo de credenciales de Firebase.',
                'debug' => ['path' => $path, 'exists' => false]
            ], 500);
        }

        // 3. Intento de Envío
        $push = new \App\Services\PushNotificationService();
        $sent = $push->sendToUser(
            $user->id,
            'Prueba de Notificación',
            '¡Si lees esto, las notificaciones funcionan correctamente! 🚀',
            ['type' => 'test']
        );

        if ($sent) {
            return response()->json(['message' => 'Notificación enviada exitosamente.', 'tokens_found' => $count]);
        } else {
            return response()->json([
                'error' => 'Fallo al enviar a Firebase.', 
                'possibilities' => [
                    'Firebase Init Failed' => 'Revisar logs (storage/logs/laravel.log)',
                    'Token Invalid' => 'El token guardado podría haber expirado o ser inválido.',
                    'Service Account' => 'El archivo json podría no tener permisos o ser incorrecto.'
                ],
                'debug_tokens_count' => $count
            ], 500);
        }
    }
}
