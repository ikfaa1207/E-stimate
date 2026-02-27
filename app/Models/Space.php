<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_area_sqm',
        'category',
        'is_active',
    ];

    protected $casts = [
        'default_area_sqm' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
