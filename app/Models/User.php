<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

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
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    // ---- Helpers ----
    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission(string $perm): bool
    {
        // permisos directos
        if ($this->permissions()->where('name', $perm)->exists()) {
            return true;
        }

        // permisos por rol
        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $perm))
            ->exists();
    }
}
