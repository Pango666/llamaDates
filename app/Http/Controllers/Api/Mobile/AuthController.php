<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * POST /api/v1/mobile/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Datos inválidos', 'details' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        $user = auth('api')->user();

        // Verificar si es paciente
        if ($user->role !== 'paciente' && !$user->patient_id) {
             // Opcional: permitir si es admin probando, pero idealmente bloquear.
             // return response()->json(['error' => 'Acceso denegado. Solo pacientes.'], 403);
        }

        if ($user->status !== 'active') {
             auth('api')->logout();
             return response()->json(['error' => 'Cuenta suspendida o inactiva. Contacta con administración.'], 403);
        }

        return $this->respondWithToken($token, $user);
    }

    /**
     * GET /api/v1/mobile/me
     */
    public function me()
    {
        $user = auth('api')->user();
        
        // Cargar datos de paciente si existen
        $patient = \App\Models\Patient::where('user_id', $user->id)
                    ->orWhere('email', $user->email) // Fallback
                    ->first();

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'patient' => $patient ? [
                'id'         => $patient->id,
                'first_name' => $patient->first_name,
                'last_name'  => $patient->last_name,
                'ci'         => $patient->ci,
                'phone'      => $patient->phone,
                'address'    => $patient->address,
                'photo_url'  => $patient->photo_path ? asset('storage/'.$patient->photo_path) : null,
            ] : null
        ]);
    }

    /**
     * POST /api/v1/mobile/logout
     */
    public function logout()
    {
        try {
            auth('api')->logout();
            return response()->json(['message' => 'Sesión cerrada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo cerrar sesión'], 500);
        }
    }

    /**
     * POST /api/v1/mobile/refresh
     */
    public function refresh()
    {
        try {
            return $this->respondWithToken(auth('api')->refresh(), auth('api')->user());
        } catch (\Exception $e) {
             return response()->json(['error' => 'Token inválido o expirado'], 401);
        }
    }

    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user_name'    => $user->name,
            'user_role'    => $user->role,
        ]);
    }

    /**
     * POST /api/v1/mobile/change-password
     */
    public function changePassword(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed', 
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validación fallida', 'details' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
             return response()->json(['error' => 'La contraseña actual no es correcta'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }
}
