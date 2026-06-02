<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralConstructionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project_with_general_specifications(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Commercial Mall project',
            'client_name' => 'Acme Corp',
            'lot_area' => 1000.00,
            'notes' => 'Build a commercial mall',
            'building_type' => 'commercial',
            'structural_type' => 'steel',
            'foundation_type' => 'pile',
            'number_of_floors' => 3,
            'gross_floor_area' => 2500.00,
            'footprint_area' => 800.00,
            'finish_level' => 'standard',
        ]);

        $project = Project::latest()->first();

        $response->assertRedirect(route('projects.show', $project));

        $this->assertEquals('commercial', $project->building_type);
        $this->assertEquals('steel', $project->structural_type);
        $this->assertEquals('pile', $project->foundation_type);
        $this->assertEquals(3, $project->number_of_floors);
        $this->assertEquals(2500.00, (float)$project->gross_floor_area);
        $this->assertEquals(800.00, (float)$project->footprint_area);

        // Verify compliances were auto-populated
        $this->assertGreaterThan(0, $project->compliances()->count());
        $this->assertTrue($project->compliances()->where('name', 'Environmental Compliance Certificate (ECC) / CNC')->exists());
        $this->assertTrue($project->compliances()->where('name', 'OSHS Safety Program Approval')->exists());

        // Verify phases were auto-populated
        $this->assertCount(8, $project->phases);
        $foundationPhase = $project->phases()->where('name', 'Earthworks & Foundation')->first();
        $this->assertNotNull($foundationPhase);
        // Verify pile driving task exists
        $this->assertTrue($foundationPhase->tasks()->where('name', 'like', '%Pile driving%')->exists());
    }

    public function test_user_can_update_compliance_clearances(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::query()->create([
            'name' => 'Residential House A',
            'client_name' => 'John Doe',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 2,
            'gross_floor_area' => 120.00,
            'footprint_area' => 60.00,
            'finish_level' => 'standard',
        ]);

        $compliance = $project->compliances()->create([
            'name' => 'Barangay Clearance',
            'type' => 'clearance',
            'status' => 'not_started',
            'fee' => 500.00,
        ]);

        $response = $this->actingAs($user)->put(route('projects.compliance.update', [$project, $compliance]), [
            'status' => 'approved',
            'fee' => 600.00,
            'target_date' => '2026-06-10',
            'approved_at' => '2026-06-02',
            'remarks' => 'Approved by barangay captain',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $compliance->refresh();

        $this->assertEquals('approved', $compliance->status);
        $this->assertEquals(600.00, (float)$compliance->fee);
        $this->assertEquals('2026-06-02', $compliance->approved_at->format('Y-m-d'));
        $this->assertEquals('Approved by barangay captain', $compliance->remarks);
    }

    public function test_user_can_update_tasks_and_complete_phases(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::query()->create([
            'name' => 'Residential House B',
            'client_name' => 'Jane Doe',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 80.00,
            'footprint_area' => 80.00,
            'finish_level' => 'standard',
        ]);

        $phase = $project->phases()->create([
            'name' => 'Earthworks',
            'sequence' => 1,
            'status' => 'pending',
        ]);

        $task1 = $phase->tasks()->create([
            'name' => 'Excavation',
            'status' => 'pending',
            'estimated_cost' => 5000.00,
            'actual_cost' => 0.00,
        ]);

        $task2 = $phase->tasks()->create([
            'name' => 'Backfill',
            'status' => 'pending',
            'estimated_cost' => 3000.00,
            'actual_cost' => 0.00,
        ]);

        // Start task 1
        $this->actingAs($user)->put(route('projects.tasks.update', [$project, $task1]), [
            'status' => 'in_progress',
            'actual_cost' => 1000.00,
            'start_date' => '2026-06-01',
            'remarks' => 'Began digging',
        ]);

        $phase->refresh();
        $this->assertEquals('in_progress', $phase->status);

        // Complete task 1
        $this->actingAs($user)->put(route('projects.tasks.update', [$project, $task1]), [
            'status' => 'completed',
            'actual_cost' => 4500.00,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-03',
            'remarks' => 'Excavation finished',
        ]);

        // Complete task 2
        $this->actingAs($user)->put(route('projects.tasks.update', [$project, $task2]), [
            'status' => 'completed',
            'actual_cost' => 3200.00,
            'start_date' => '2026-06-03',
            'end_date' => '2026-06-04',
            'remarks' => 'Backfilling finished',
        ]);

        $phase->refresh();
        $this->assertEquals('completed', $phase->status);
        $this->assertEquals('2026-06-04', $phase->end_date->format('Y-m-d'));
    }

    public function test_guest_can_access_project_via_share_token(): void
    {
        $project = Project::query()->create([
            'name' => 'Shared Mall Project',
            'client_name' => 'Guest Company',
            'building_type' => 'commercial',
            'structural_type' => 'steel',
            'foundation_type' => 'pile',
            'number_of_floors' => 2,
            'gross_floor_area' => 1000.00,
            'footprint_area' => 500.00,
            'finish_level' => 'standard',
        ]);

        $this->assertNotNull($project->share_token);

        $this->withSession(["project_share_verified_{$project->id}" => true]);

        $response = $this->get(route('projects.share.show', $project->share_token));

        $response->assertStatus(200);
        $response->assertSee('Shared Mall Project');
        $response->assertSee('Guest Company');
    }

    public function test_guest_can_approve_locked_estimate_via_share_token(): void
    {
        $project = Project::query()->create([
            'name' => 'Shared Building Project',
            'client_name' => 'Client Company',
            'building_type' => 'commercial',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 500.00,
            'footprint_area' => 500.00,
            'finish_level' => 'standard',
        ]);

        $estimate = \App\Models\Estimate::query()->create([
            'project_id' => $project->id,
            'finish_level' => 'standard',
            'gross_floor_area' => 500.00,
            'wall_area' => 675.00,
            'roof_area' => 550.00,
            'slab_area' => 500.00,
            'total_cost' => 250000.00,
            'cost_per_sqm' => 500.00,
            'generated_at' => now(),
            'status' => 'locked',
        ]);

        $this->withSession(["project_share_verified_{$project->id}" => true]);

        $response = $this->post(route('projects.share.approve', $project->share_token));

        $response->assertRedirect(route('projects.share.show', $project->share_token));
        $this->assertEquals('approved', $estimate->refresh()->status);
    }

    public function test_user_can_update_compliance_clearances_via_ajax(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::query()->create([
            'name' => 'Residential House A',
            'client_name' => 'John Doe',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 2,
            'gross_floor_area' => 120.00,
            'footprint_area' => 60.00,
            'finish_level' => 'standard',
        ]);

        $compliance = $project->compliances()->create([
            'name' => 'Barangay Clearance',
            'type' => 'clearance',
            'status' => 'not_started',
            'fee' => 500.00,
        ]);

        $response = $this->actingAs($user)->putJson(route('projects.compliance.update', [$project, $compliance]), [
            'status' => 'approved',
            'fee' => 600.00,
            'target_date' => '2026-06-10',
            'approved_at' => '2026-06-02',
            'remarks' => 'Approved by AJAX',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'compliance' => [
                'id' => $compliance->id,
                'status' => 'approved',
                'fee' => 600.00,
                'remarks' => 'Approved by AJAX',
            ]
        ]);
        
        $compliance->refresh();
        $this->assertEquals('approved', $compliance->status);
    }

    public function test_user_can_update_tasks_via_ajax(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::query()->create([
            'name' => 'Residential House B',
            'client_name' => 'Jane Doe',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 80.00,
            'footprint_area' => 80.00,
            'finish_level' => 'standard',
        ]);

        $phase = $project->phases()->create([
            'name' => 'Earthworks',
            'sequence' => 1,
            'status' => 'pending',
        ]);

        $task = $phase->tasks()->create([
            'name' => 'Excavation',
            'status' => 'pending',
            'estimated_cost' => 5000.00,
            'actual_cost' => 0.00,
        ]);

        $response = $this->actingAs($user)->putJson(route('projects.tasks.update', [$project, $task]), [
            'status' => 'completed',
            'actual_cost' => 4500.00,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-03',
            'remarks' => 'Excavation finished by AJAX',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'status' => 'completed',
                'actual_cost' => 4500.00,
            ],
            'phase' => [
                'id' => $phase->id,
                'status' => 'completed',
            ],
            'project' => [
                'progress_percent' => 100,
                'estimated_total' => 5000,
                'actual_total' => 4500,
                'balance' => 500,
            ]
        ]);
        
        $task->refresh();
        $this->assertEquals('completed', $task->status);
    }

    public function test_task_and_phase_overdue_and_delayed_states(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::query()->create([
            'name' => 'Overdue Test Project',
            'client_name' => 'Jane Doe',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 80.00,
            'footprint_area' => 80.00,
            'finish_level' => 'standard',
        ]);

        $phase = $project->phases()->create([
            'name' => 'Earthworks',
            'sequence' => 1,
            'status' => 'pending',
        ]);

        $task = $phase->tasks()->create([
            'name' => 'Excavation',
            'status' => 'pending',
            'estimated_cost' => 5000.00,
            'actual_cost' => 0.00,
            'target_date' => now()->subDays(2)->toDateString(), // 2 days ago
        ]);

        // Verify task and phase are overdue/delayed
        $this->assertTrue($task->isOverdue());
        $this->assertTrue($phase->isDelayed());

        // Update task target_date to future
        $response = $this->actingAs($user)->putJson(route('projects.tasks.update', [$project, $task]), [
            'status' => 'in_progress',
            'actual_cost' => 1000.00,
            'target_date' => now()->addDays(5)->toDateString(), // 5 days in the future
        ]);

        $response->assertStatus(200);
        $task->refresh();
        $phase->refresh();

        // Should no longer be overdue
        $this->assertFalse($task->isOverdue());
        $this->assertFalse($phase->isDelayed());
        $this->assertEquals('in_progress', $task->status);

        // Reset to overdue
        $task->update(['target_date' => now()->subDays(1)->toDateString()]);
        $task->refresh();
        $phase->refresh();
        $this->assertTrue($task->isOverdue());
        $this->assertTrue($phase->isDelayed());

        // Complete the task, which should clear the overdue status even if target_date is in the past
        $response = $this->actingAs($user)->putJson(route('projects.tasks.update', [$project, $task]), [
            'status' => 'completed',
            'actual_cost' => 5000.00,
            'target_date' => now()->subDays(1)->toDateString(),
        ]);

        $response->assertStatus(200);
        $task->refresh();
        $phase->refresh();

        $this->assertFalse($task->isOverdue());
        $this->assertFalse($phase->isDelayed());
    }

    public function test_project_update_synchronizes_compliances_and_workflows(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        
        // 1. Create a residential project (will not have ECC compliance)
        $project = Project::query()->create([
            'name' => 'Project to Sync',
            'client_name' => 'John Client',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 2,
            'gross_floor_area' => 150.00,
            'footprint_area' => 75.00,
            'finish_level' => 'standard',
        ]);
        
        app(\App\Services\ProjectSetupService::class)->setup($project);

        $this->assertFalse($project->compliances()->where('name', 'Environmental Compliance Certificate (ECC) / CNC')->exists());
        $foundationPhase = $project->phases()->where('sequence', 2)->first();
        $this->assertTrue($foundationPhase->tasks()->where('name', 'like', '%Excavation of column footings%')->exists());
        $this->assertFalse($foundationPhase->tasks()->where('name', 'like', '%Pile driving%')->exists());

        // 2. Update to commercial and pile foundation
        $response = $this->actingAs($user)->put(route('projects.update', $project), [
            'name' => 'Project to Sync',
            'client_name' => 'John Client',
            'building_type' => 'commercial', // changed
            'structural_type' => 'concrete',
            'foundation_type' => 'pile', // changed
            'number_of_floors' => 2,
            'gross_floor_area' => 150.00,
            'footprint_area' => 75.00,
            'finish_level' => 'standard',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $project->refresh();

        // 3. Assert ECC compliance was created
        $this->assertTrue($project->compliances()->where('name', 'Environmental Compliance Certificate (ECC) / CNC')->exists());
        
        // 4. Assert foundation tasks were updated to pile foundation
        $foundationPhase->refresh();
        $this->assertFalse($foundationPhase->tasks()->where('name', 'like', '%Excavation of column footings%')->exists());
        $this->assertTrue($foundationPhase->tasks()->where('name', 'like', '%Pile driving%')->exists());
    }
}
