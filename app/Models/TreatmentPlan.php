<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentPlan extends Model
{
    protected $table = 'treatment_plans';

    protected $fillable = [
        'patient_id',
        'title',
        'estimate_total',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'estimate_total' => 'decimal:2',
        'approved_at'    => 'datetime',
    ];

    // Relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Alias items/treatments (ya lo tenías)
    public function items()
    {
        return $this->hasMany(Treatment::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    // Citas generadas a partir del plan
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'treatment_plan_id');
    }

    public function updateEstimateTotal(): void
    {
        $this->update([
            'estimate_total' => $this->treatments()->sum('price'),
        ]);
    }
}
