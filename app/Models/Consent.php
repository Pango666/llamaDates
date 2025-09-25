<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Consent extends Model
{
    protected $table = 'consents';
    protected $fillable = [
        'patient_id',
        'appointment_id',
        'template_id',
        'title',
        'body',
        'signed_at',
        'signed_by_name',
        'signed_by_doc',
        'signature_path',
        'file_path',
    ];
    protected $dates = ['signed_at'];
    protected $casts = ['signed_at' => 'datetime'];

    protected $appends = ['signature_url'];

    public function getSignatureUrlAttribute()
    {
        return $this->signature_path ? Storage::disk('public')->url($this->signature_path) : null;
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function template()
    {
        return $this->belongsTo(ConsentTemplate::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
