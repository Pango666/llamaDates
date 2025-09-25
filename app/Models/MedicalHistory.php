<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalHistory extends Model
{
    protected $table = 'medical_histories';
    protected $fillable = [
        'patient_id',
        'smoker',
        'pregnant',
        'allergies',
        'medications',
        'systemic_diseases',
        'surgical_history',
        'habits',
        'extra'
    ];
    protected $casts = [
        'smoker' => 'boolean',
        'pregnant' => 'boolean',
        'extra' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
