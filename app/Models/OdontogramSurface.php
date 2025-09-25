<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdontogramSurface extends Model
{
    protected $table = 'odontogram_surfaces';
    protected $fillable = ['odontogram_tooth_id','surface','condition','notes'];

    public function tooth(){ return $this->belongsTo(OdontogramTooth::class,'odontogram_tooth_id'); }
}
