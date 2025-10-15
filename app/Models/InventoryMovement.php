<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'type',
        'qty',
        'unit_cost',
        'lot',
        'expires_at',
        'appointment_id',
        'user_id',
        'note'
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'unit_cost' => 'decimal:4',
        'expires_at' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
