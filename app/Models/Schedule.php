<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'dentist_id',
        'day_of_week',
        'start_time',
        'end_time',
        'breaks',
    ];

    protected $casts = [
        'breaks' => 'array', // <- IMPORTANTÍSIMO
    ];

    public function dentist()
    {
        return $this->belongsTo(Dentist::class);
    }

    public function chair()
    {
        return $this->belongsTo(Chair::class);
    }
}
