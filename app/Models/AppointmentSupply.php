<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentSupply extends Model
{
    protected $fillable = [
        'appointment_id',
        'product_id',
        'location_id',
        'lot',
        'qty',
        'unit_cost_at_issue'
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'unit_cost_at_issue' => 'decimal:4',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
