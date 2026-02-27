<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estimate extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_requirement_id',
        'finish_level',
        'gross_floor_area',
        'wall_area',
        'roof_area',
        'slab_area',
        'total_cost',
        'cost_per_sqm',
        'generated_at',
    ];

    protected $casts = [
        'gross_floor_area' => 'decimal:2',
        'wall_area' => 'decimal:2',
        'roof_area' => 'decimal:2',
        'slab_area' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'cost_per_sqm' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectRequirement(): BelongsTo
    {
        return $this->belongsTo(ProjectRequirement::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(EstimateLine::class);
    }

    public function breakdowns(): HasMany
    {
        return $this->hasMany(EstimateBreakdown::class);
    }
}
