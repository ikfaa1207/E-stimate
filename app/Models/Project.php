<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'share_token',
        'share_passcode',
        'name',
        'client_name',
        'lot_area',
        'notes',
        'building_type',
        'structural_type',
        'foundation_type',
        'number_of_floors',
        'gross_floor_area',
        'footprint_area',
        'finish_level',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->share_token)) {
                $project->share_token = \Illuminate\Support\Str::random(32);
            }
            if (empty($project->share_passcode)) {
                $project->share_passcode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $casts = [
        'lot_area' => 'decimal:2',
        'gross_floor_area' => 'decimal:2',
        'footprint_area' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function requirement(): HasOne
    {
        return $this->hasOne(ProjectRequirement::class);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }

    public function compliances(): HasMany
    {
        return $this->hasMany(ProjectCompliance::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('sequence');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class);
    }
}

