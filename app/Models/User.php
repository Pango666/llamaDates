<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // ===== JWT =====
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function patient()
    {
        return $this->hasOne(\App\Models\Patient::class);
    }

    public function isPatient(): bool
    {
        return in_array($this->role, ['patient', 'paciente'], true);
    }

    public function dentist()
    {
        return $this->hasOne(Dentist::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Permisos vía roles + permisos directos.
     */
    public function allPermissionNames(): \Illuminate\Support\Collection
    {
        $fromRoles = $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name');

        $direct = $this->permissions()->pluck('name');

        return $fromRoles->merge($direct)->unique();
    }

    public function hasRole(string $role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }


    public function hasPermission(string $permission): bool
    {

        if ($this->permissions()->where('name', $permission)->exists()) {
            return true;
        }


        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $permission))
            ->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        $all = $this->allPermissionNames();
        foreach ($permissions as $p) {
            if ($all->contains($p)) {
                return true;
            }
        }
        return false;
    }
}
