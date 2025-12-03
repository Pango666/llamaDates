<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function appointmentSupplies()
    {
        return $this->hasMany(AppointmentSupply::class);
    }
}
