<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\Space;
use App\Services\BuildingCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_building_metrics_from_requirements_and_spaces(): void
    {
        Space::query()->create(['name' => 'Bedroom', 'category' => 'bedroom', 'default_area_sqm' => 12, 'is_active' => true]);
        Space::query()->create(['name' => 'Bathroom', 'category' => 'bathroom', 'default_area_sqm' => 4, 'is_active' => true]);
        Space::query()->create(['name' => 'Kitchen', 'category' => 'kitchen', 'default_area_sqm' => 10, 'is_active' => true]);
        Space::query()->create(['name' => 'Garage', 'category' => 'garage', 'default_area_sqm' => 18, 'is_active' => true]);
        Space::query()->create(['name' => 'Living Room', 'category' => 'living_room', 'default_area_sqm' => 16, 'is_active' => true]);

        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client',
        ]);

        $requirement = ProjectRequirement::query()->create([
            'project_id' => $project->id,
            'number_of_floors' => 2,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'garage_count' => 1,
            'living_rooms' => 1,
            'kitchen_count' => 1,
            'finish_level' => ProjectRequirement::FINISH_LEVEL_STANDARD,
        ]);

        $result = app(BuildingCalculator::class)->calculate($requirement);

        $this->assertEquals(176.00, $result['total_space_area']);
        $this->assertEquals(220.00, $result['gross_floor_area']);
        $this->assertEquals(297.00, $result['wall_area']);
        $this->assertEquals(242.00, $result['roof_area']);
        $this->assertEquals(220.00, $result['slab_area']);
    }

    public function test_it_applies_space_area_overrides(): void
    {
        Space::query()->create(['name' => 'Bedroom', 'category' => 'bedroom', 'default_area_sqm' => 12, 'is_active' => true]);
        Space::query()->create(['name' => 'Bathroom', 'category' => 'bathroom', 'default_area_sqm' => 4, 'is_active' => true]);
        Space::query()->create(['name' => 'Kitchen', 'category' => 'kitchen', 'default_area_sqm' => 10, 'is_active' => true]);
        Space::query()->create(['name' => 'Garage', 'category' => 'garage', 'default_area_sqm' => 18, 'is_active' => true]);
        Space::query()->create(['name' => 'Living Room', 'category' => 'living_room', 'default_area_sqm' => 16, 'is_active' => true]);

        $project = Project::query()->create([
            'name' => 'Test Project Overrides',
            'client_name' => 'Client',
        ]);

        $requirement = ProjectRequirement::query()->create([
            'project_id' => $project->id,
            'number_of_floors' => 2,
            'bedrooms' => 3, // overridden to 15
            'bathrooms' => 2,
            'garage_count' => 1,
            'living_rooms' => 1,
            'kitchen_count' => 1,
            'finish_level' => ProjectRequirement::FINISH_LEVEL_STANDARD,
            'space_area_overrides' => [
                'bedroom' => 15,
            ]
        ]);

        $result = app(BuildingCalculator::class)->calculate($requirement);

        $this->assertEquals(194.00, $result['total_space_area']);
        $this->assertEquals(242.50, $result['gross_floor_area']);
        $this->assertEquals(327.38, $result['wall_area']);
        $this->assertEquals(266.75, $result['roof_area']);
        $this->assertEquals(242.50, $result['slab_area']);
    }
}
