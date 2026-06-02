<?php

namespace Tests\Feature;

use App\Models\Estimate;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCollaborationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_post_comment_and_revision_request_on_task(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        app(\App\Services\ProjectSetupService::class)->setup($project);
        $task = $project->phases->first()->tasks->first();

        // 1. Post normal comment
        $response = $this->actingAs($user)->postJson(route('projects.comments.store', $project), [
            'project_task_id' => $task->id,
            'content' => 'This is a general comment.',
            'type' => 'comment',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('project_comments', [
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'content' => 'This is a general comment.',
            'type' => 'comment',
            'status' => null,
        ]);

        // 2. Post revision request
        $response2 = $this->actingAs($user)->postJson(route('projects.comments.store', $project), [
            'project_task_id' => $task->id,
            'content' => 'Please revise this cost.',
            'type' => 'revision_request',
        ]);

        $response2->assertStatus(200);
        $this->assertDatabaseHas('project_comments', [
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'content' => 'Please revise this cost.',
            'type' => 'revision_request',
            'status' => 'pending',
        ]);
    }

    public function test_guest_can_post_comment_and_revision_request_via_share_token(): void
    {
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'share_token' => 'test-token-xyz',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        app(\App\Services\ProjectSetupService::class)->setup($project);
        $task = $project->phases->first()->tasks->first();

        $this->withSession(["project_share_verified_{$project->id}" => true]);

        // Guest comment on task
        $response = $this->postJson(route('projects.share.comments.store', 'test-token-xyz'), [
            'project_task_id' => $task->id,
            'author_name' => 'Guest Client Bob',
            'content' => 'Hello from Bob.',
            'type' => 'comment',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('project_comments', [
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'author_name' => 'Guest Client Bob',
            'author_role' => 'guest',
            'content' => 'Hello from Bob.',
            'type' => 'comment',
        ]);
    }

    public function test_admin_can_resolve_revision_requests(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        $comment = ProjectComment::query()->create([
            'project_id' => $project->id,
            'author_name' => 'Guest',
            'author_role' => 'guest',
            'content' => 'Revise this please',
            'type' => 'revision_request',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->postJson(route('projects.comments.resolve', [$project, $comment]));

        $response->assertStatus(200);
        $this->assertEquals('resolved', $comment->refresh()->status);
    }

    public function test_non_authorized_role_cannot_resolve_revision_requests(): void
    {
        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'client_id' => $client->id,
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        $comment = ProjectComment::query()->create([
            'project_id' => $project->id,
            'author_name' => 'Guest',
            'author_role' => 'guest',
            'content' => 'Revise this please',
            'type' => 'revision_request',
            'status' => 'pending',
        ]);

        // Client (not contractor/admin) tries to resolve
        $response = $this->actingAs($client)->postJson(route('projects.comments.resolve', [$project, $comment]));
        $response->assertStatus(403);
        $this->assertEquals('pending', $comment->refresh()->status);
    }

    public function test_admin_can_delete_comments(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        $comment = ProjectComment::query()->create([
            'project_id' => $project->id,
            'author_name' => 'Guest',
            'author_role' => 'guest',
            'content' => 'Revise this please',
            'type' => 'comment',
        ]);

        $response = $this->actingAs($admin)->deleteJson(route('projects.comments.destroy', [$project, $comment]));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('project_comments', ['id' => $comment->id]);
    }

    public function test_revision_request_on_locked_estimate_automatically_transitions_its_status_to_revision_pending(): void
    {
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'share_token' => 'test-token-abc',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        $estimate = Estimate::query()->create([
            'project_id' => $project->id,
            'finish_level' => 'standard',
            'gross_floor_area' => 100,
            'wall_area' => 100,
            'roof_area' => 100,
            'slab_area' => 100,
            'total_cost' => 50000.00,
            'cost_per_sqm' => 500.00,
            'generated_at' => now(),
            'status' => 'locked',
        ]);

        $this->withSession(["project_share_verified_{$project->id}" => true]);

        $response = $this->postJson(route('projects.share.comments.store', 'test-token-abc'), [
            'estimate_id' => $estimate->id,
            'author_name' => 'Guest Client Bob',
            'content' => 'Please lower the cost of standard floor tiles.',
            'type' => 'revision_request',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('revision_pending', $estimate->refresh()->status);
    }

    public function test_revision_request_on_approved_estimate_is_blocked_backend(): void
    {
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'share_token' => 'test-token-approved-est',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        $estimate = Estimate::query()->create([
            'project_id' => $project->id,
            'finish_level' => 'standard',
            'gross_floor_area' => 100,
            'wall_area' => 100,
            'roof_area' => 100,
            'slab_area' => 100,
            'total_cost' => 50000.00,
            'cost_per_sqm' => 500.00,
            'generated_at' => now(),
            'status' => 'approved',
        ]);

        $this->withSession(["project_share_verified_{$project->id}" => true]);

        $response = $this->postJson(route('projects.share.comments.store', 'test-token-approved-est'), [
            'estimate_id' => $estimate->id,
            'author_name' => 'Guest Client Bob',
            'content' => 'Please lower the cost of standard floor tiles.',
            'type' => 'revision_request',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertDatabaseMissing('project_comments', [
            'content' => 'Please lower the cost of standard floor tiles.',
        ]);
    }

    public function test_task_level_revision_request_is_blocked_if_estimate_is_approved(): void
    {
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'share_token' => 'test-token-task-approved',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        app(\App\Services\ProjectSetupService::class)->setup($project);
        $task = $project->phases->first()->tasks->first();

        $estimate = Estimate::query()->create([
            'project_id' => $project->id,
            'finish_level' => 'standard',
            'gross_floor_area' => 100,
            'wall_area' => 100,
            'roof_area' => 100,
            'slab_area' => 100,
            'total_cost' => 50000.00,
            'cost_per_sqm' => 500.00,
            'generated_at' => now(),
            'status' => 'approved',
        ]);

        $this->withSession(["project_share_verified_{$project->id}" => true]);

        $response = $this->postJson(route('projects.share.comments.store', 'test-token-task-approved'), [
            'project_task_id' => $task->id,
            'author_name' => 'Guest Client Bob',
            'content' => 'Please revise task cost.',
            'type' => 'revision_request',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertDatabaseMissing('project_comments', [
            'content' => 'Please revise task cost.',
        ]);

        // General comments on tasks should still be allowed even after approval
        $response2 = $this->postJson(route('projects.share.comments.store', 'test-token-task-approved'), [
            'project_task_id' => $task->id,
            'author_name' => 'Guest Client Bob',
            'content' => 'This is a allowed comment.',
            'type' => 'comment',
        ]);
        $response2->assertStatus(200);
        $this->assertDatabaseHas('project_comments', [
            'content' => 'This is a allowed comment.',
            'type' => 'comment',
        ]);
    }
}
