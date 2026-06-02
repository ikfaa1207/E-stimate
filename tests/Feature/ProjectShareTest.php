<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_to_auth_if_not_verified(): void
    {
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'share_token' => 'test-token-xyz',
            'share_passcode' => '123456',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        $response = $this->get(route('projects.share.show', 'test-token-xyz'));

        $response->assertRedirect(route('projects.share.auth', 'test-token-xyz'));
    }

    public function test_guest_gets_401_on_ajax_comment_if_not_verified(): void
    {
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'share_token' => 'test-token-xyz',
            'share_passcode' => '123456',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        $response = $this->postJson(route('projects.share.comments.store', 'test-token-xyz'), [
            'content' => 'Ajax comment should fail',
            'type' => 'comment',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
    }

    public function test_guest_fails_with_incorrect_passcode(): void
    {
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'share_token' => 'test-token-xyz',
            'share_passcode' => '123456',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        $response = $this->post(route('projects.share.verify', 'test-token-xyz'), [
            'passcode' => '654321', // incorrect
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('passcode');
        $this->assertFalse(session()->has("project_share_verified_{$project->id}"));
    }

    public function test_guest_succeeds_with_correct_passcode(): void
    {
        $project = Project::query()->create([
            'name' => 'Test Project',
            'client_name' => 'Client X',
            'share_token' => 'test-token-xyz',
            'share_passcode' => '123456',
            'building_type' => 'residential',
            'structural_type' => 'concrete',
            'foundation_type' => 'footing',
            'number_of_floors' => 1,
            'gross_floor_area' => 100,
            'footprint_area' => 100,
            'finish_level' => 'standard',
        ]);

        // Access the verification route with the correct PIN
        $response = $this->post(route('projects.share.verify', 'test-token-xyz'), [
            'passcode' => '123456',
        ]);

        $response->assertRedirect(route('projects.share.show', 'test-token-xyz'));
        $this->assertTrue(session("project_share_verified_{$project->id}"));

        // Now guest can view the page successfully
        $response2 = $this->get(route('projects.share.show', 'test-token-xyz'));
        $response2->assertStatus(200);
        $response2->assertViewIs('projects.share');
    }
}
