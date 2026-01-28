<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'contact',
        'phone',
        'tax_id',
        'active',
    ];

    // Un proveedor tiene muchos productos
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
