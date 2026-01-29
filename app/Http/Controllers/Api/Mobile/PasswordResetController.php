<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    /**
     * POST /api/v1/mobile/password/email
     * Enviar enlace de restablecimiento
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);

        if ($validator->fails()) {
            return response()->json(['error' => 'Email inválido'], 422);
        }

        // We use the standard Laravel broker
        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
                    ? response()->json(['message' => 'Enlace de restablecimiento enviado a tu correo.'])
                    : response()->json(['error' => 'No se pudo enviar el correo.'], 400);
    }
}
