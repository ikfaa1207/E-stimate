<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    public const TYPE_MATERIAL = 'material';

    public const TYPE_LABOR = 'labor';

    public const TYPE_EQUIPMENT = 'equipment';

    public const TYPES = [
        self::TYPE_MATERIAL,
        self::TYPE_LABOR,
        self::TYPE_EQUIPMENT,
    ];

    protected $fillable = [
        'name',
        'type',
        'unit',
        'unit_cost',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    public function assemblyItems(): HasMany
    {
        return $this->hasMany(AssemblyItem::class);
    }

    public function assemblies(): BelongsToMany
    {
        return $this->belongsToMany(Assembly::class, 'assembly_items')
            ->withPivot('qty_per_unit')
            ->withTimestamps();
    }
}
