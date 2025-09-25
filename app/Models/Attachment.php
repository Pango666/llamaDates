<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $table = 'attachments';
    protected $fillable = [
        'patient_id',
        'appointment_id',
        'clinical_note_id',
        'type',
        'path',
        'original_name',
        'notes'
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

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
