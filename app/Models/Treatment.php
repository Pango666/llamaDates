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
        'dentist_id',
        'planned_date',
        'planned_start_time',
        'planned_end_time',
        'surface',
        'price',
        'status',
        'appointment_id',
        'notes'
    ];
    
    protected $casts = [
        'price'              => 'decimal:2',
        'planned_date'       => 'date',
        'planned_start_time' => 'datetime:H:i',
        'planned_end_time'   => 'datetime:H:i',
    ];

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

    public function dentist()
    {
        return $this->belongsTo(Dentist::class);
    }

    
    protected static function booted()
    {
        static::saved(function (Treatment $treatment) {
            if ($treatment->plan) {
                $treatment->plan->updateEstimateTotal();
            }
        });

        static::deleted(function (Treatment $treatment) {
            if ($treatment->plan) {
                $treatment->plan->updateEstimateTotal();
            }
        });
    }
}
