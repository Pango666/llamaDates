<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    protected $table = 'diagnoses';
    protected $fillable = ['patient_id', 'appointment_id','code', 'label', 'tooth_code', 'surface', 'status', 'notes'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function clinicalNote()
    {
        return $this->belongsTo(ClinicalNote::class);
    }
}
