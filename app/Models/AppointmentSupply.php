<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentSupply extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'product_id',
        'location_id',
        'lot',
        'qty',
        'unit_cost_at_issue',
    ];

    protected $casts = [
        'qty'               => 'integer',
        'unit_cost_at_issue'=> 'decimal:4',
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
