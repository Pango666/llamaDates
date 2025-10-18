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
        $cred = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        $user = User::where('email', $cred['email'])->first();
        if (!$user || $user->status !== 'active') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Usuario inexistente o suspendido.',
            ]);
        }

        if (!\Illuminate\Support\Facades\Auth::attempt($cred, $remember)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Credenciales inválidas.',
            ]);
        }

        $request->session()->regenerate();


        return redirect()->to(match ($user->role) {
            'admin'      => route('admin.dashboard'),
            'recepcion'  => route('recepcion.dashboard'),
            'odontologo' => route('odontologo.dashboard'),
            'paciente'   => route('app.dashboard'),
            default      => route('dashboard'),
        })->with('ok', 'Bienvenido');
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
