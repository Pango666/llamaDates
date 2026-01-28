<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the user profile view.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $tab = $request->get('tab', 'datos');

        return view('admin.profile', compact('user', 'tab'));
    }

    /**
     * Update basic user information.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Add other generic fields here if needed in the future
        ]);

        $user = $request->user();
        $user->update([
            'name' => $request->input('name'),
        ]);

        return back()->with('ok', 'Perfil actualizado correctamente.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('ok', 'Contraseña actualizada correctamente.');
    }
}
