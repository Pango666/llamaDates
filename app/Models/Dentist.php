<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dentist extends Model
{
    protected $fillable = ['user_id', 'name', 'ci', 'address', 'specialty', 'chair_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function chair()
    {
        return $this->belongsTo(Chair::class)->withDefault();
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
