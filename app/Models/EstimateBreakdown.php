<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateBreakdown extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }
}
