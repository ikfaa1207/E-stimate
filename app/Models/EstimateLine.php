<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'metric_name',
        'assembly_id',
        'item_name',
        'quantity',
        'unit',
        'unit_cost',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }
}
