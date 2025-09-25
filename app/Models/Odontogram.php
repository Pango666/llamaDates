<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odontogram extends Model
{
    protected $table = 'odontograms';
    protected $fillable = ['patient_id', 'appointment_id','date', 'notes', 'created_by'];
    protected $casts = ['date' => 'date'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function teeth()
    {
        return $this->hasMany(OdontogramTooth::class);
    }
}
