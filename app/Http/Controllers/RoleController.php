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
        $roles = Role::when($q, fn($qq) => $qq->where('name', 'like', "%{$q}%"))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.roles.index', compact('roles', 'q'));
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
        $role->delete();
        return back()->with('ok', 'Rol eliminado.');
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
