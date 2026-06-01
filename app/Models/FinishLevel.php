<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'material_multiplier',
        'labor_multiplier',
        'equipment_multiplier',
        'is_active',
    ];

    protected $casts = [
        'material_multiplier' => 'decimal:2',
        'labor_multiplier' => 'decimal:2',
        'equipment_multiplier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getMultiplierForType(string $type): float
    {
        return match ($type) {
            Item::TYPE_MATERIAL => (float) $this->material_multiplier,
            Item::TYPE_LABOR => (float) $this->labor_multiplier,
            Item::TYPE_EQUIPMENT => (float) $this->equipment_multiplier,
            default => 1.00,
        };
    }
}
