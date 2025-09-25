<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdontogramTooth extends Model
{
     protected $table = 'odontogram_teeth';
    protected $fillable = ['odontogram_id','tooth_code','status','notes'];

    public function odontogram(){ return $this->belongsTo(Odontogram::class); }
    public function surfaces(){ return $this->hasMany(OdontogramSurface::class,'odontogram_tooth_id'); }
}
