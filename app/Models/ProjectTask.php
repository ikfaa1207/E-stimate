<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_phase_id',
        'name',
        'status',
        'estimated_cost',
        'actual_cost',
        'start_date',
        'end_date',
        'target_date',
        'remarks',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'target_date' => 'date',
    ];

    /**
     * Determine if the task is overdue based on target date.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'completed' 
            && $this->target_date 
            && $this->target_date->isPast();
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'project_phase_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class, 'project_task_id');
    }
}
