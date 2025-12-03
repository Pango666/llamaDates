<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPresentationUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Una unidad de presentación tiene muchos productos
    public function products()
    {
        return $this->hasMany(Product::class, 'presentation_unit_id');
    }
}
