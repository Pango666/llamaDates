<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));
        $status = $r->get('status');

        $roles = Role::when($q, fn($qq) => $qq->where('name', 'like', "%{$q}%"))
            ->when($status === 'active', fn($qq) => $qq->where('is_active', true))
            ->when($status === 'inactive', fn($qq) => $qq->where('is_active', false))
            ->orderBy('name')->paginate(20)->withQueryString();

        $totals = [
            'total'    => Role::count(),
            'active'   => Role::where('is_active', true)->count(),
            'inactive' => Role::where('is_active', false)->count(),
        ];

        return view('admin.roles.index', compact('roles', 'q', 'totals'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:60', 'unique:roles,name'],
            'label' => ['nullable', 'string', 'max:120'],
        ]);
        Role::create($data);
        return redirect()->route('admin.roles.index')->with('ok', 'Rol creado.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $r, Role $role)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('roles', 'name')->ignore($role->id)],
            'label' => ['nullable', 'string', 'max:120'],
        ]);
        $role->update($data);
        return redirect()->route('admin.roles.index')->with('ok', 'Rol actualizado.');
    }

    public function destroy(Role $role)
    {
        // El usuario pidió "solo desactivar", así que redirigimos a toggle o damos error.
        // Mejor lo dejamos como "Hard Delete" solo por si acaso, pero borramos el botón en la vista.
        $role->delete();
        return back()->with('ok', 'Rol eliminado.');
    }

    public function toggle(Role $role)
    {
        $newState = !$role->is_active;
        $role->update(['is_active' => $newState]);

        if (!$newState) {
            // "cuando se desactive un rol todos los usuarios con ese rol se desactivan"
            // Asumiendo que el campo 'role' en users guarda el 'name' del rol (basado en UserController)
            \App\Models\User::where('role', $role->name)->update(['status' => 'suspended']);
        }

        $msg = $newState ? 'Rol activado.' : 'Rol desactivado y usuarios asociados suspendidos.';
        return back()->with('ok', $msg);
    }

    /** asignación de permisos a un rol */
    public function editPerms(Role $role)
    {
        $perms = Permission::orderBy('name')->get();
        $role->load('permissions');
        return view('admin.roles.perms', compact('role', 'perms'));
    }

    public function updatePerms(Request $r, Role $role)
    {
        $data = $r->validate([
            'perms' => ['array'],
            'perms.*' => ['integer', 'exists:permissions,id'],
        ]);
        $role->permissions()->sync($data['perms'] ?? []);
        return redirect()->route('admin.roles.index')->with('ok', 'Permisos actualizados para el rol.');
    }
}
