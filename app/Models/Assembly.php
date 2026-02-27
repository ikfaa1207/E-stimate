<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assembly extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
    ];

    public function assemblyItems(): HasMany
    {
        return $this->hasMany(AssemblyItem::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'assembly_items')
            ->withPivot('qty_per_unit')
            ->withTimestamps();
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(AssemblyMapping::class);
    }
}
