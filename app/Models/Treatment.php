<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Treatment extends Model
{
    protected $table = 'treatments';
    protected $fillable = [
        'treatment_plan_id',
        'service_id',
        'tooth_code',
        'surface',
        'price',
        'status',
        'appointment_id',
        'notes'
    ];
    protected $casts = ['price' => 'decimal:2'];

    public function plan()
    {
        return $this->belongsTo(TreatmentPlan::class, 'treatment_plan_id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    protected static function booted()
    {
        static::saved(function ($treatment) {
            $treatment->plan->updateEstimateTotal();
        });

        static::deleted(function ($treatment) {
            $treatment->plan->updateEstimateTotal();
        });
    }
}
