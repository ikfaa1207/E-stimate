<?php

namespace Tests\Feature;

use App\Models\Estimate;
use App\Models\EstimateAdjustment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimateLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_cannot_lock_or_unlock_estimates(): void
    {
        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $project = Project::query()->create(['name' => 'Project A', 'client_name' => 'Client A', 'client_id' => $client->id]);
        $estimate = Estimate::query()->create([
            'project_id' => $project->id,
            'finish_level' => 'standard',
            'gross_floor_area' => 100,
            'wall_area' => 120,
            'roof_area' => 110,
            'slab_area' => 100,
            'total_cost' => 50000,
            'cost_per_sqm' => 500,
            'generated_at' => now(),
            'status' => 'draft',
        ]);

        $this->actingAs($client)
            ->post(route('projects.estimates.lock', [$project, $estimate]))
            ->assertStatus(403);

        $this->actingAs($client)
            ->post(route('projects.estimates.unlock', [$project, $estimate]))
            ->assertStatus(403);
    }

    public function test_estimator_and_admin_can_lock_and_unlock_estimates(): void
    {
        $estimator = User::factory()->create(['role' => User::ROLE_ESTIMATOR]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::query()->create(['name' => 'Project A', 'client_name' => 'Client A']);
        $estimate = Estimate::query()->create([
            'project_id' => $project->id,
            'finish_level' => 'standard',
            'gross_floor_area' => 100,
            'wall_area' => 120,
            'roof_area' => 110,
            'slab_area' => 100,
            'total_cost' => 50000,
            'cost_per_sqm' => 500,
            'generated_at' => now(),
            'status' => 'draft',
        ]);

        // 1. Estimator locks
        $response = $this->actingAs($estimator)
            ->post(route('projects.estimates.lock', [$project, $estimate]));
        $response->assertRedirect(route('projects.estimates.show', [$project, $estimate]));
        $this->assertEquals('approved', $estimate->refresh()->status);

        // 2. Estimator unlocks
        $response = $this->actingAs($estimator)
            ->post(route('projects.estimates.unlock', [$project, $estimate]));
        $response->assertRedirect(route('projects.estimates.show', [$project, $estimate]));
        $this->assertEquals('draft', $estimate->refresh()->status);

        // 3. Admin locks
        $response = $this->actingAs($admin)
            ->post(route('projects.estimates.lock', [$project, $estimate]));
        $response->assertRedirect(route('projects.estimates.show', [$project, $estimate]));
        $this->assertEquals('approved', $estimate->refresh()->status);
    }

    public function test_modifying_adjustments_is_blocked_on_locked_estimates(): void
    {
        $estimator = User::factory()->create(['role' => User::ROLE_ESTIMATOR]);
        $project = Project::query()->create(['name' => 'Project A', 'client_name' => 'Client A']);
        $estimate = Estimate::query()->create([
            'project_id' => $project->id,
            'finish_level' => 'standard',
            'gross_floor_area' => 100,
            'wall_area' => 120,
            'roof_area' => 110,
            'slab_area' => 100,
            'total_cost' => 50000,
            'cost_per_sqm' => 500,
            'generated_at' => now(),
            'status' => 'approved', // Locked
        ]);

        $adjustment = EstimateAdjustment::query()->create([
            'estimate_id' => $estimate->id,
            'name' => 'Adjustment 1',
            'amount' => 1000,
            'type' => 'material',
        ]);

        // Store blocked
        $this->actingAs($estimator)
            ->post(route('projects.estimates.adjustments.store', [$project, $estimate]), [
                'name' => 'Adjustment 2',
                'amount' => 500,
                'type' => 'labor',
            ])
            ->assertStatus(403);

        // Update blocked
        $this->actingAs($estimator)
            ->put(route('projects.estimates.adjustments.update', [$project, $estimate, $adjustment]), [
                'name' => 'Adjustment 1 Edited',
                'amount' => 2000,
                'type' => 'material',
            ])
            ->assertStatus(403);

        // Destroy blocked
        $this->actingAs($estimator)
            ->delete(route('projects.estimates.adjustments.destroy', [$project, $estimate, $adjustment]))
            ->assertStatus(403);
    }
}
