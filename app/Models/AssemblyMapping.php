<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssemblyMapping extends Model
{
    use HasFactory;

    public const METRIC_WALL_AREA = 'wall_area';

    public const METRIC_ROOF_AREA = 'roof_area';

    public const METRIC_SLAB_AREA = 'slab_area';

    public const METRICS = [
        self::METRIC_WALL_AREA,
        self::METRIC_ROOF_AREA,
        self::METRIC_SLAB_AREA,
    ];

    protected $fillable = [
        'metric_name',
        'assembly_id',
    ];

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }
}
