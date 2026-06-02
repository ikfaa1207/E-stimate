<?php

namespace App\Services;

use App\Models\Project;

class BuildingCalculator
{
    public function calculate(Project $project): array
    {
        $grossFloorArea = (float) $project->gross_floor_area;
        $footprintArea = (float) $project->footprint_area;
        $floors = max(1, (int) $project->number_of_floors);
        $totalSpaceArea = $grossFloorArea;

        if ($grossFloorArea <= 0 && $project->requirement) {
            $quantities = [
                'bedroom' => $project->requirement->bedrooms,
                'bathroom' => $project->requirement->bathrooms,
                'garage' => $project->requirement->garage_count,
                'living_room' => $project->requirement->living_rooms,
                'kitchen' => $project->requirement->kitchen_count,
            ];

            $spaces = \App\Models\Space::query()
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
                $overrides = $project->requirement->space_area_overrides;
                if (is_array($overrides) && isset($overrides[$category]) && (float) $overrides[$category] > 0) {
                    $area = (float) $overrides[$category];
                }
                $singleFloorArea += $area * $quantity;
            }
            $floors = max(1, $project->requirement->number_of_floors);
            $totalSpaceArea = $singleFloorArea * $floors;
            $grossFloorArea = $totalSpaceArea * (float) config('estimation.circulation_factor', 1.25);

            $wallArea = $grossFloorArea * (float) config('estimation.wall_area_factor', 1.35);
            $roofArea = $grossFloorArea * (float) config('estimation.roof_area_factor', 1.10);
            $slabArea = $grossFloorArea * (float) config('estimation.slab_area_factor', 1.00);
        } else {
            if ($grossFloorArea <= 0) {
                $grossFloorArea = 100.00;
                $totalSpaceArea = $grossFloorArea;
            }

            if ($footprintArea <= 0) {
                $footprintArea = $grossFloorArea / $floors;
            }

            $wallArea = $grossFloorArea * (float) config('estimation.wall_area_factor', 1.35);
            $roofArea = $footprintArea * (float) config('estimation.roof_area_factor', 1.10);
            $slabArea = $footprintArea * (float) config('estimation.slab_area_factor', 1.00) * $floors;
        }

        return [
            'total_space_area' => round($totalSpaceArea, 2),
            'gross_floor_area' => round($grossFloorArea, 2),
            'wall_area' => round($wallArea, 2),
            'roof_area' => round($roofArea, 2),
            'slab_area' => round($slabArea, 2),
        ];
    }
}
