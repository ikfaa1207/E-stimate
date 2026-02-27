<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssemblyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'assembly_id',
        'item_id',
        'qty_per_unit',
    ];

    protected $casts = [
        'qty_per_unit' => 'decimal:4',
    ];

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
