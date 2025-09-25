<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentTemplate extends Model
{
    protected $fillable = ['name','body', 'active'];
}
