<?php

namespace Tests\Feature\Api\V1\Workspace;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceAssignedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_user_can_only_see_assigned_workspaces(): void
    {
        $userA = User::factory()->create();
        $userA->assignRole('admin');

        $userB = User::factory()->create();
        $userB->assignRole('admin');

        $projectA = Project::create([
            'name'       => 'Project A',
            'created_by' => $userA->id,
        ]);

        $projectB = Project::create([
            'name'       => 'Project B',
            'created_by' => $userB->id,
        ]);

        // Workspace A belongs to User A
        $workspaceA = Workspace::create([
            'project_id' => $projectA->id,
            'name'       => 'Workspace Alpha',
            'created_by' => $userA->id,
        ]);
        $workspaceA->users()->attach($userA->id, ['role' => 'creator']);

        // Workspace B belongs to User B
        $workspaceB = Workspace::create([
            'project_id' => $projectB->id,
            'name'       => 'Workspace Beta',
            'created_by' => $userB->id,
        ]);
        $workspaceB->users()->attach($userB->id, ['role' => 'creator']);

        // User A requests workspaces
        $responseA = $this->actingAs($userA, 'sanctum')->getJson('/api/workspaces');
        $responseA->assertStatus(200);

        $workspaceIdsA = collect($responseA->json('data'))->pluck('id');
        $this->assertTrue($workspaceIdsA->contains($workspaceA->id));
        $this->assertFalse($workspaceIdsA->contains($workspaceB->id));

        // User B requests workspaces
        $responseB = $this->actingAs($userB, 'sanctum')->getJson('/api/workspaces');
        $responseB->assertStatus(200);

        $workspaceIdsB = collect($responseB->json('data'))->pluck('id');
        $this->assertTrue($workspaceIdsB->contains($workspaceB->id));
        $this->assertFalse($workspaceIdsB->contains($workspaceA->id));
    }

    public function test_user_can_only_see_projects_of_assigned_workspaces_or_created_projects(): void
    {
        $userA = User::factory()->create();
        $userA->assignRole('admin');

        $userB = User::factory()->create();
        $userB->assignRole('admin');

        $projectA = Project::create([
            'name'       => 'Project Alpha',
            'created_by' => $userA->id,
        ]);

        $projectB = Project::create([
            'name'       => 'Project Beta',
            'created_by' => $userB->id,
        ]);

        $workspaceA = Workspace::create([
            'project_id' => $projectA->id,
            'name'       => 'Workspace Alpha',
            'created_by' => $userA->id,
        ]);
        $workspaceA->users()->attach($userA->id, ['role' => 'creator']);

        $workspaceB = Workspace::create([
            'project_id' => $projectB->id,
            'name'       => 'Workspace Beta',
            'created_by' => $userB->id,
        ]);
        $workspaceB->users()->attach($userB->id, ['role' => 'creator']);

        // User A requests projects
        $responseA = $this->actingAs($userA, 'sanctum')->getJson('/api/projects');
        $responseA->assertStatus(200);

        $projectIdsA = collect($responseA->json('data'))->pluck('id');
        $this->assertTrue($projectIdsA->contains($projectA->id));
        $this->assertFalse($projectIdsA->contains($projectB->id));

        // User B requests projects
        $responseB = $this->actingAs($userB, 'sanctum')->getJson('/api/projects');
        $responseB->assertStatus(200);

        $projectIdsB = collect($responseB->json('data'))->pluck('id');
        $this->assertTrue($projectIdsB->contains($projectB->id));
        $this->assertFalse($projectIdsB->contains($projectA->id));
    }
}
