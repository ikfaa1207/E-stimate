<?php

namespace App\Services;

use App\Models\ProjectRequirement;
use App\Models\Space;

class BuildingCalculator
{
    public function calculate(ProjectRequirement $requirement): array
    {
        $quantities = [
            'bedroom' => $requirement->bedrooms,
            'bathroom' => $requirement->bathrooms,
            'garage' => $requirement->garage_count,
            'living_room' => $requirement->living_rooms,
            'kitchen' => $requirement->kitchen_count,
        ];

        $spaces = Space::query()
            ->where('is_active', true)
            ->whereIn('category', array_keys($quantities))
            ->get()
            ->keyBy('category');

        $singleFloorArea = 0.0;

        foreach ($quantities as $category => $quantity) {
            $space = $spaces->get($category);

            if (! $space || $quantity <= 0) {
                continue;
            }

            $area = (float) $space->default_area_sqm;
            $overrides = $requirement->space_area_overrides;
            if (is_array($overrides) && isset($overrides[$category]) && (float) $overrides[$category] > 0) {
                $area = (float) $overrides[$category];
            }

            $singleFloorArea += $area * $quantity;
        }

        $totalSpaceArea = $singleFloorArea * max(1, $requirement->number_of_floors);
        $grossFloorArea = $totalSpaceArea * (float) config('estimation.circulation_factor', 1.25);
        $wallArea = $grossFloorArea * (float) config('estimation.wall_area_factor', 1.35);
        $roofArea = $grossFloorArea * (float) config('estimation.roof_area_factor', 1.10);
        $slabArea = $grossFloorArea * (float) config('estimation.slab_area_factor', 1.00);

        return [
            'total_space_area' => round($totalSpaceArea, 2),
            'gross_floor_area' => round($grossFloorArea, 2),
            'wall_area' => round($wallArea, 2),
            'roof_area' => round($roofArea, 2),
            'slab_area' => round($slabArea, 2),
        ];
    }
}
