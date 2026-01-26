<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!auth()->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Credenciales inválidas'])
                ->withInput();
        }

        $user = auth()->user();

        // VALIDACIÓN: Usuario activo
        if ($user->status !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Tu cuenta está desactivada o suspendida.'])
                ->withInput();
        }

        // Cargamos roles si existe la relación
        if (method_exists($user, 'roles')) {
            $user->loadMissing('roles');
        }

        // ¿Tiene rol paciente?
        $hasPatientRole = method_exists($user, 'hasRole')
            ? $user->hasRole('paciente')
            : (($user->role ?? null) === 'paciente');

        // ¿Tiene algún rol staff además de paciente?
        $staffRoles = ['admin', 'asistente', 'odontologo', 'cajero', 'enfermera', 'almacen'];
        $hasStaffRole = method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole($staffRoles)
            : in_array(($user->role ?? null), $staffRoles, true);

        if ($hasStaffRole) {
            // Gana el panel admin
            return redirect()->route('admin.dashboard');
        }

        if ($hasPatientRole) {
            return redirect()->route('app.dashboard');
        }

        // Si por alguna razón no tiene nada claro, lo mandamos al admin por defecto
        return redirect()->route('admin.dashboard');
    }


    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('ok', 'Sesión cerrada.');
    }

    // --- Reset de contraseña ---
    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Usa Password::sendResetLink (requiere notificación por mail configurada)
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('ok', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showReset(string $token)
    {
        return view('auth.reset', ['token' => $token, 'email' => request('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('ok', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
