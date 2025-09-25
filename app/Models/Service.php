<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable=['name','duration_min','price','active'];

    protected $casts = [
        'breaks' => 'array',   
    ];

    public function getDurationMinAttribute($value)
    {
        if (is_numeric($value) && (int)$value > 0) return (int)$value;

        // compatibilidad con duration_minutes
        $alt = $this->attributes['duration_min'] ?? null;
        if (is_numeric($alt) && (int)$alt > 0) return (int)$alt;

        return 30; // fallback seguro
    }
}
