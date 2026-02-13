<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Appointment extends Model
{
    use Auditable;
    protected $fillable = [
        'patient_id',
        'dentist_id',
        'service_id',
        'chair_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'is_active',
        'canceled_at',
        'canceled_by',
        'canceled_reason',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'date'        => 'date',
        'canceled_at' => 'datetime',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->where('status', '!=', 'canceled');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function dentist()
    {
        return $this->belongsTo(Dentist::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // app/Models/Appointment.php


    public function getDateOnlyAttribute(): string
    {
        return Carbon::parse($this->date)->toDateString();
    }

    public function getStartAtAttribute(): Carbon
    {
        return Carbon::parse($this->date_only)->setTimeFromTimeString($this->start_time);
    }

    public function getEndAtAttribute(): Carbon
    {
        return Carbon::parse($this->date_only)->setTimeFromTimeString($this->end_time);
    }

    public function clinicalNotes()
    {
        return $this->hasMany(ClinicalNote::class);
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
