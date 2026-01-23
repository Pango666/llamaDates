<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));
        $users = User::query()
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            }))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $perms = Permission::orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'perms'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:120'],
            'phone' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'role'  => ['required', Rule::in(['admin', 'asistente', 'odontologo', 'paciente', 'cajero', 'almacen', 'enfermera'])],
            'roles' => ['array'],              // ids de la tabla roles
            'roles.*' => ['integer', 'exists:roles,id'],
            'perms' => ['array'],
            'perms.*' => ['integer', 'exists:permissions,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'role' => $data['role'], // sincroniza enum “rol principal”
        ]);

        $user->roles()->sync($data['roles'] ?? []);
        $user->permissions()->sync($data['perms'] ?? []);

        return redirect()->route('admin.users.index')->with('ok', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $perms = Permission::orderBy('name')->get();
        $user->load(['roles', 'permissions']);
        return view('admin.users.edit', compact('user', 'roles', 'perms'));
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'max:120'],
            'phone' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'role'  => ['required', Rule::in(['admin', 'asistente', 'odontologo', 'paciente', 'cajero', 'almacen', 'enfermera'])],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'perms' => ['array'],
            'perms.*' => ['integer', 'exists:permissions,id'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'role' => $data['role'],
            ...(isset($data['password']) && $data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $user->roles()->sync($data['roles'] ?? []);
        $user->permissions()->sync($data['perms'] ?? []);

        return redirect()->route('admin.users.index')->with('ok', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('ok', 'Usuario eliminado.');
    }
}
