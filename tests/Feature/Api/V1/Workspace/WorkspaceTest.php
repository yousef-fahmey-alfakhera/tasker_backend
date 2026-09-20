<?php

namespace Tests\Feature\Api\V1\Workspace;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->project = Project::create([
            'name'       => 'Core Platform',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_guest_cannot_access_workspaces(): void
    {
        $response = $this->getJson('/api/workspaces');
        $response->assertStatus(401);
    }

    public function test_user_can_create_workspace_with_assigned_users_and_roles(): void
    {
        $adminUser = User::factory()->create();
        $editorUser = User::factory()->create();
        $itUser = User::factory()->create();

        $payload = [
            'project_id'  => $this->project->id,
            'name'        => 'Backend Engineering Workspace',
            'description' => 'API and Database architecture',
            'users'       => [
                ['user_id' => $adminUser->id, 'role' => 'admin'],
                ['user_id' => $editorUser->id, 'role' => 'editor'],
                ['user_id' => $itUser->id, 'role' => 'it'],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/workspaces', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Workspace created successfully.',
                'data'    => [
                    'name'       => 'Backend Engineering Workspace',
                    'project_id' => $this->project->id,
                ],
            ]);

        $workspaceId = $response->json('data.id');

        $this->assertDatabaseHas('workspace_users', [
            'workspace_id' => $workspaceId,
            'user_id'      => $this->user->id,
            'role'         => 'creator',
        ]);

        $this->assertDatabaseHas('workspace_users', [
            'workspace_id' => $workspaceId,
            'user_id'      => $adminUser->id,
            'role'         => 'admin',
        ]);

        $this->assertDatabaseHas('workspace_users', [
            'workspace_id' => $workspaceId,
            'user_id'      => $itUser->id,
            'role'         => 'it',
        ]);
    }

    public function test_user_can_show_workspace(): void
    {
        $workspace = Workspace::create([
            'project_id' => $this->project->id,
            'name'       => 'QA Testing Workspace',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/workspaces/' . $workspace->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'id'   => $workspace->id,
                    'name' => 'QA Testing Workspace',
                ],
            ]);
    }

    public function test_user_can_update_workspace(): void
    {
        $workspace = Workspace::create([
            'project_id' => $this->project->id,
            'name'       => 'Old Workspace Name',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/workspaces/' . $workspace->id, [
                'name' => 'Renamed Workspace',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => ['name' => 'Renamed Workspace'],
            ]);
    }

    public function test_user_can_delete_workspace(): void
    {
        $workspace = Workspace::create([
            'project_id' => $this->project->id,
            'name'       => 'Workspace to Delete',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/workspaces/' . $workspace->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Workspace deleted successfully.',
            ]);

        $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
    }
}
