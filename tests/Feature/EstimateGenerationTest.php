<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimateGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_generate_estimate_from_project_requirements(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'Residential Lot A',
            'client_name' => 'John Client',
            'lot_area' => 120,
            'notes' => 'Initial client meeting',
        ]);

        ProjectRequirement::query()->create([
            'project_id' => $project->id,
            'number_of_floors' => 2,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'garage_count' => 1,
            'living_rooms' => 1,
            'kitchen_count' => 1,
            'finish_level' => ProjectRequirement::FINISH_LEVEL_STANDARD,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('projects.estimates.store', $project));

        $project->refresh();
        $estimate = $project->estimates()->latest('generated_at')->first();

        $response->assertRedirect(route('projects.estimates.show', [$project, $estimate]));

        $this->assertNotNull($estimate);
        $this->assertGreaterThan(0, (float) $estimate->gross_floor_area);
        $this->assertGreaterThan(0, (float) $estimate->total_cost);
        $this->assertCount(3, $estimate->lines);
        $this->assertCount(3, $estimate->breakdowns);
    }

    public function test_authenticated_user_can_manage_estimate_adjustments(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'Residential Lot B',
            'client_name' => 'Jane Client',
            'lot_area' => 150,
        ]);

        $requirement = ProjectRequirement::query()->create([
            'project_id' => $project->id,
            'number_of_floors' => 1,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'garage_count' => 0,
            'living_rooms' => 1,
            'kitchen_count' => 1,
            'finish_level' => ProjectRequirement::FINISH_LEVEL_STANDARD,
        ]);

        $estimate = app(\App\Services\EstimateGenerator::class)->generate($project);
        $baseTotalCost = (float) $estimate->total_cost;
        $baseMaterialCost = (float) $estimate->breakdowns()->where('type', 'material')->value('amount');

        $response = $this
            ->actingAs($user)
            ->post(route('projects.estimates.adjustments.store', [$project, $estimate]), [
                'name' => 'Structural steel upgrade',
                'amount' => 5000,
                'type' => 'material',
            ]);

        $response->assertRedirect(route('projects.estimates.show', [$project, $estimate]));
        $estimate->refresh();

        $this->assertCount(1, $estimate->adjustments);
        $this->assertEquals(round($baseTotalCost + 5000, 2), (float) $estimate->total_cost);
        $this->assertEquals(round($baseMaterialCost + 5000, 2), (float) $estimate->breakdowns()->where('type', 'material')->value('amount'));

        $adjustment = $estimate->adjustments->first();

        $response = $this
            ->actingAs($user)
            ->put(route('projects.estimates.adjustments.update', [$project, $estimate, $adjustment]), [
                'name' => 'Structural steel upgrade edited',
                'amount' => -2000,
                'type' => 'labor',
            ]);

        $response->assertRedirect(route('projects.estimates.show', [$project, $estimate]));
        $estimate->refresh();
        $adjustment->refresh();

        $this->assertEquals('Structural steel upgrade edited', $adjustment->name);
        $this->assertEquals(-2000, (float) $adjustment->amount);
        $this->assertEquals('labor', $adjustment->type);
        $this->assertEquals($baseMaterialCost, (float) $estimate->breakdowns()->where('type', 'material')->value('amount'));
        
        $baseLaborCost = (float) $estimate->breakdowns()->where('type', 'labor')->value('amount');
        $this->assertEquals(round($baseTotalCost - 2000, 2), (float) $estimate->total_cost);

        $response = $this
            ->actingAs($user)
            ->delete(route('projects.estimates.adjustments.destroy', [$project, $estimate, $adjustment]));

        $response->assertRedirect(route('projects.estimates.show', [$project, $estimate]));
        $estimate->refresh();

        $this->assertCount(0, $estimate->adjustments);
        $this->assertEquals(round($baseTotalCost, 2), (float) $estimate->total_cost);
    }

    public function test_estimate_generation_applies_per_type_multipliers_correctly(): void
    {
        $this->seed(DatabaseSeeder::class);

        $finishLevel = \App\Models\FinishLevel::where('name', 'premium')->first();
        $finishLevel->update([
            'material_multiplier' => 1.50,
            'labor_multiplier' => 2.00,
            'equipment_multiplier' => 0.50,
        ]);

        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'Custom Multiplier Project',
            'client_name' => 'Premium Client',
            'lot_area' => 100,
        ]);

        $requirement = ProjectRequirement::query()->create([
            'project_id' => $project->id,
            'number_of_floors' => 1,
            'bedrooms' => 1,
            'bathrooms' => 0,
            'garage_count' => 0,
            'living_rooms' => 0,
            'kitchen_count' => 0,
            'finish_level' => 'premium',
        ]);

        $estimate = app(\App\Services\EstimateGenerator::class)->generate($project);

        $this->assertNotNull($estimate);
        $this->assertEquals('premium', $estimate->finish_level);

        $chbWallLine = $estimate->lines()->where('metric_name', 'wall_area')->first();
        $this->assertEquals(664.50, (float) $chbWallLine->unit_cost);
        $this->assertEquals(round(20.25 * 664.5, 2), (float) $chbWallLine->line_total);
    }
}
