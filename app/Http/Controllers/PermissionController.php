<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));
        $perms = Permission::when($q, fn($qq) => $qq->where('name', 'like', "%{$q}%"))
            ->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.permissions.index', compact('perms', 'q'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'  => ['required', 'string', 'max:120', 'unique:permissions,name'],
            'label' => ['nullable', 'string', 'max:150'],
        ]);
        Permission::create($data);
        return redirect()->route('admin.permissions.index')->with('ok', 'Permiso creado.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $r, Permission $permission)
    {
        $data = $r->validate([
            'name'  => ['required', 'string', 'max:120', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'label' => ['nullable', 'string', 'max:150'],
        ]);
        $permission->update($data);
        return redirect()->route('admin.permissions.index')->with('ok', 'Permiso actualizado.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return back()->with('ok', 'Permiso eliminado.');
    }
}
