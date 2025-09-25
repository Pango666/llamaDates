<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalNote extends Model
{
    protected $table = 'clinical_notes';
    protected $fillable = [
        'patient_id',
        'appointment_id',
        'type',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'vitals',
        'author_id'
    ];
    
    protected $casts = ['vitals' => 'array'];

    //relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
