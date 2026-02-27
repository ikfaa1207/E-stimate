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
}
