<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_cannot_access_master_data_routes(): void
    {
        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);

        $this->actingAs($client)->get(route('spaces.index'))->assertStatus(403);
        $this->actingAs($client)->get(route('items.index'))->assertStatus(403);
        $this->actingAs($client)->get(route('assemblies.index'))->assertStatus(403);
    }

    public function test_estimator_can_view_master_data_but_cannot_modify(): void
    {
        $estimator = User::factory()->create(['role' => User::ROLE_ESTIMATOR]);
        $space = Space::query()->create(['name' => 'Space A', 'default_area_sqm' => 10, 'category' => 'bedroom']);

        // Can view index
        $this->actingAs($estimator)->get(route('spaces.index'))->assertStatus(200);

        // Cannot modify
        $this->actingAs($estimator)->post(route('spaces.store'), [
            'name' => 'Space B',
            'default_area_sqm' => 12,
            'category' => 'bedroom',
        ])->assertStatus(403);

        $this->actingAs($estimator)->put(route('spaces.update', $space), [
            'name' => 'Space A Edited',
            'default_area_sqm' => 15,
            'category' => 'bedroom',
        ])->assertStatus(403);

        $this->actingAs($estimator)->delete(route('spaces.destroy', $space))->assertStatus(403);
    }

    public function test_admin_can_modify_master_data(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $space = Space::query()->create(['name' => 'Space A', 'default_area_sqm' => 10, 'category' => 'bedroom']);

        $this->actingAs($admin)->put(route('spaces.update', $space), [
            'name' => 'Space A Edited',
            'default_area_sqm' => 15,
            'category' => 'bedroom',
        ])->assertStatus(302); // Redirect back on success
    }

    public function test_client_can_only_see_their_own_projects(): void
    {
        $clientA = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $clientB = User::factory()->create(['role' => User::ROLE_CLIENT]);

        $projectA = Project::query()->create(['name' => 'Project A', 'client_name' => 'Client A', 'client_id' => $clientA->id]);
        $projectB = Project::query()->create(['name' => 'Project B', 'client_name' => 'Client B', 'client_id' => $clientB->id]);

        // Client A views project list
        $response = $this->actingAs($clientA)->get(route('projects.index'));
        $response->assertStatus(200);
        $response->assertSee('Project A');
        $response->assertDontSee('Project B');

        // Client A shows project A (success)
        $this->actingAs($clientA)->get(route('projects.show', $projectA))->assertStatus(200);

        // Client A shows project B (forbidden)
        $this->actingAs($clientA)->get(route('projects.show', $projectB))->assertStatus(403);
    }

    public function test_client_cannot_create_or_modify_projects(): void
    {
        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $project = Project::query()->create(['name' => 'Project A', 'client_name' => 'Client A', 'client_id' => $client->id]);

        $this->actingAs($client)->get(route('projects.create'))->assertStatus(403);
        
        $this->actingAs($client)->post(route('projects.store'), [
            'name' => 'New Project',
            'client_name' => 'Client Name',
        ])->assertStatus(403);

        $this->actingAs($client)->get(route('projects.edit', $project))->assertStatus(403);

        $this->actingAs($client)->put(route('projects.update', $project), [
            'name' => 'Project A Edited',
            'client_name' => 'Client A',
        ])->assertStatus(403);

        $this->actingAs($client)->delete(route('projects.destroy', $project))->assertStatus(403);
    }

    public function test_client_cannot_access_finish_levels(): void
    {
        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);

        $this->actingAs($client)->get(route('finish-levels.index'))->assertStatus(403);
    }

    public function test_estimator_can_view_finish_levels_but_cannot_modify(): void
    {
        $estimator = User::factory()->create(['role' => User::ROLE_ESTIMATOR]);
        $finishLevel = \App\Models\FinishLevel::query()->create([
            'name' => 'luxury',
            'display_name' => 'Luxury',
            'material_multiplier' => 1.5,
            'labor_multiplier' => 1.3,
            'equipment_multiplier' => 1.1,
            'is_active' => true,
        ]);

        $this->actingAs($estimator)->get(route('finish-levels.index'))->assertStatus(200);

        $this->actingAs($estimator)->post(route('finish-levels.store'), [
            'name' => 'custom',
            'display_name' => 'Custom',
            'material_multiplier' => 1.1,
            'labor_multiplier' => 1.1,
            'equipment_multiplier' => 1.1,
        ])->assertStatus(403);

        $this->actingAs($estimator)->put(route('finish-levels.update', $finishLevel), [
            'name' => 'luxury-edited',
            'display_name' => 'Luxury Edited',
            'material_multiplier' => 1.6,
            'labor_multiplier' => 1.3,
            'equipment_multiplier' => 1.1,
        ])->assertStatus(403);

        $this->actingAs($estimator)->delete(route('finish-levels.destroy', $finishLevel))->assertStatus(403);
    }

    public function test_admin_can_modify_finish_levels(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $finishLevel = \App\Models\FinishLevel::query()->create([
            'name' => 'luxury',
            'display_name' => 'Luxury',
            'material_multiplier' => 1.5,
            'labor_multiplier' => 1.3,
            'equipment_multiplier' => 1.1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->put(route('finish-levels.update', $finishLevel), [
            'name' => 'luxury',
            'display_name' => 'Super Luxury',
            'material_multiplier' => 1.6,
            'labor_multiplier' => 1.4,
            'equipment_multiplier' => 1.2,
        ])->assertStatus(302);

        $this->assertDatabaseHas('finish_levels', [
            'name' => 'luxury',
            'display_name' => 'Super Luxury',
            'material_multiplier' => '1.60',
            'labor_multiplier' => '1.40',
            'equipment_multiplier' => '1.20',
        ]);
    }
}
