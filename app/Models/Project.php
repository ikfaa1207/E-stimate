<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_name',
        'lot_area',
        'notes',
    ];

    protected $casts = [
        'lot_area' => 'decimal:2',
    ];

    public function requirement(): HasOne
    {
        return $this->hasOne(ProjectRequirement::class);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }
}
