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
}
