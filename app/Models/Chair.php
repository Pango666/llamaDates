<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chair extends Model
{
    protected $table = 'chairs';
    protected $fillable = ['name', 'shift'];

    public const SHIFT_MAÑANA  = 'mañana';
    public const SHIFT_TARDE   = 'tarde';
    public const SHIFT_COMPLETO= 'completo';

    public function dentists()
    {
        return $this->hasMany(Dentist::class);
    }

    public static function shiftWindow(string $shift): array {
        return match ($shift) {
            self::SHIFT_MAÑANA   => ['08:00','13:59:59'],
            self::SHIFT_TARDE    => ['14:00','22:00:00'],
            default              => ['00:00','23:59:59'],
        };
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
