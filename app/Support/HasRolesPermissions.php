<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;

trait HasRolesPermissions
{
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = (array)$roles;
        return $this->roles()->whereIn('name', $roles)->exists()
            || in_array($this->role, $roles, true); // compatibilidad con enum column
    }

    public function hasPermission(string $permission): bool
    {
        // directo al usuario
        if ($this->permissions()->where('name', $permission)->exists()) return true;

        // por rol
        return $this->roles()->whereHas('permissions', fn($q)=>$q->where('name',$permission))->exists();
    }

    public function syncRoles(array $roleIds)
    {
        $this->roles()->sync($roleIds);
    }

    public function syncPermissions(array $permIds)
    {
        $this->permissions()->sync($permIds);
    }
}
