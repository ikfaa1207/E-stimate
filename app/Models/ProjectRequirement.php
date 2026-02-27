<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectRequirement extends Model
{
    use HasFactory;

    public const FINISH_LEVEL_ECONOMY = 'economy';

    public const FINISH_LEVEL_STANDARD = 'standard';

    public const FINISH_LEVEL_PREMIUM = 'premium';

    public const FINISH_LEVELS = [
        self::FINISH_LEVEL_ECONOMY,
        self::FINISH_LEVEL_STANDARD,
        self::FINISH_LEVEL_PREMIUM,
    ];

    protected $fillable = [
        'project_id',
        'number_of_floors',
        'bedrooms',
        'bathrooms',
        'garage_count',
        'living_rooms',
        'kitchen_count',
        'finish_level',
    ];

    protected $casts = [
        'number_of_floors' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'garage_count' => 'integer',
        'living_rooms' => 'integer',
        'kitchen_count' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }
}
