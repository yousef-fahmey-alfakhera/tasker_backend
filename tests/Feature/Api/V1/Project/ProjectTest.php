<?php

namespace Tests\Feature\Api\V1\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_projects(): void
    {
        $response = $this->getJson('/api/projects');
        $response->assertStatus(401);
    }

    public function test_user_can_list_projects(): void
    {
        Project::create([
            'name'        => 'Marketing Campaign',
            'description' => 'Q4 Marketing',
            'created_by'  => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Projects retrieved successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'created_by'],
                ],
            ]);
    }

    public function test_user_can_create_project(): void
    {
        $payload = [
            'name'        => 'Tasker Backend',
            'description' => 'ClickUp clone API',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/projects', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Project created successfully.',
                'data'    => [
                    'name'        => 'Tasker Backend',
                    'description' => 'ClickUp clone API',
                    'created_by'  => $this->user->id,
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'name'       => 'Tasker Backend',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_user_can_show_project(): void
    {
        $project = Project::create([
            'name'       => 'Website Redesign',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/projects/' . $project->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'id'   => $project->id,
                    'name' => 'Website Redesign',
                ],
            ]);
    }

    public function test_user_can_update_project_via_put_and_post(): void
    {
        $project = Project::create([
            'name'       => 'Old Project Name',
            'created_by' => $this->user->id,
        ]);

        // PUT update
        $putResponse = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/projects/' . $project->id, [
                'name' => 'Updated via PUT',
            ]);

        $putResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => ['name' => 'Updated via PUT'],
            ]);

        // POST update (multipart/form-data support)
        $postResponse = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/projects/' . $project->id, [
                'name' => 'Updated via POST',
            ]);

        $postResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => ['name' => 'Updated via POST'],
            ]);
    }

    public function test_user_can_delete_project(): void
    {
        $project = Project::create([
            'name'       => 'To Be Deleted',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/projects/' . $project->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project deleted successfully.',
            ]);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
