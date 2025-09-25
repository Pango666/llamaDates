<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = ['user_id', 'first_name', 'last_name', 'ci', 'birthdate', 'email', 'phone', 'address'];
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function medicalHistory()
    {
        return $this->hasOne(MedicalHistory::class);
    }
    public function odontograms()
    {
        return $this->hasMany(Odontogram::class);
    }
    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }
    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class);
    }
    public function clinicalNotes()
    {
        return $this->hasMany(ClinicalNote::class);
    }
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
