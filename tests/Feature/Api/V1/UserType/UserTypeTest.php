<?php

namespace Tests\Feature\Api\V1\UserType;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskType;
use App\Models\User;
use App\Models\UserType;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTypeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->admin = User::where('email', 'admin@admin.com')->first();
        $this->regularUser = User::where('email', 'user@user.com')->first();
    }

    public function test_guest_cannot_access_user_types(): void
    {
        $response = $this->getJson('/api/user-types');
        $response->assertStatus(401);
    }

    public function test_admin_can_list_user_types(): void
    {
        UserType::create([
            'user_id'     => $this->regularUser->id,
            'type'        => 'normal',
            'respnsapity' => ['focus', 'device'],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/user-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'user_id', 'type', 'respnsapity'],
                ],
            ]);
    }

    public function test_admin_can_create_user_type_with_responsibilities_array(): void
    {
        $newUser = User::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/user-types', [
            'user_id'     => $newUser->id,
            'type'        => 'it',
            'respnsapity' => ['network', 'device', 'focus'],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'user_id'     => $newUser->id,
                    'type'        => 'it',
                    'respnsapity' => ['network', 'device', 'focus'],
                ],
            ]);

        $this->assertDatabaseHas('user_types', [
            'user_id' => $newUser->id,
            'type'    => 'it',
        ]);
    }

    public function test_admin_can_update_and_delete_user_type(): void
    {
        $userType = UserType::create([
            'user_id'     => $this->regularUser->id,
            'type'        => 'manager',
            'respnsapity' => ['focus'],
        ]);

        $updateResponse = $this->actingAs($this->admin, 'sanctum')->putJson('/api/user-types/' . $userType->id, [
            'type'        => 'lead_manager',
            'respnsapity' => ['focus', 'other'],
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.type', 'lead_manager')
            ->assertJsonPath('data.respnsapity', ['focus', 'other']);

        $deleteResponse = $this->actingAs($this->admin, 'sanctum')->deleteJson('/api/user-types/' . $userType->id);
        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('user_types', ['id' => $userType->id]);
    }

    public function test_task_index_sets_respnsapity_to_one_when_task_type_matches_user_type_or_responsibilities(): void
    {
        $itUser = User::factory()->create();
        $itUser->assignRole('admin');

        // Assign IT user type with responsibilities for 'device' and 'focus'
        UserType::create([
            'user_id'     => $itUser->id,
            'type'        => 'it',
            'respnsapity' => ['device', 'focus'],
        ]);

        $project = Project::create([
            'name'       => 'Ops Project',
            'created_by' => $itUser->id,
        ]);

        $workspace = Workspace::create([
            'project_id' => $project->id,
            'name'       => 'Ops Workspace',
            'created_by' => $itUser->id,
        ]);
        $workspace->users()->attach($itUser->id, ['role' => 'creator']);

        $status = TaskStatus::create(['name' => 'Todo', 'stage' => 'pending', 'order' => 1]);

        $deviceType = TaskType::create(['name' => 'Device Repair', 'type' => 'device']);
        $otherType = TaskType::create(['name' => 'General Cleaning', 'type' => 'other']);

        // Task 1: Type 'device' (in user's respnsapity array)
        $task1 = Task::create([
            'created_by'   => $itUser->id,
            'project_id'   => $project->id,
            'workspace_id' => $workspace->id,
            'status_id'    => $status->id,
            'task_type_id' => $deviceType->id,
            'title'        => 'Fix Router Device',
            'position'     => 1,
        ]);

        // Task 2: Type 'other' (not in user's respnsapity or user type)
        $task2 = Task::create([
            'created_by'   => $itUser->id,
            'project_id'   => $project->id,
            'workspace_id' => $workspace->id,
            'status_id'    => $status->id,
            'task_type_id' => $otherType->id,
            'title'        => 'Water the Plants',
            'position'     => 2,
        ]);

        $response = $this->actingAs($itUser, 'sanctum')->getJson('/api/tasks');
        $response->assertStatus(200);

        $tasks = collect($response->json('data'))->keyBy('id');

        $this->assertEquals(1, $tasks[$task1->id]['respnsapity']);
        $this->assertEquals(0, $tasks[$task2->id]['respnsapity']);
    }

    public function test_user_without_user_type_is_filtered_to_creator_only(): void
    {
        $creatorUser = User::factory()->create();
        $creatorUser->assignRole('admin');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('admin');

        // Confirm neither has a userType
        $this->assertNull($creatorUser->userType);
        $this->assertNull($otherUser->userType);

        $project = Project::create([
            'name'       => 'Shared Project',
            'created_by' => $creatorUser->id,
        ]);

        $workspace = Workspace::create([
            'project_id' => $project->id,
            'name'       => 'Shared Workspace',
            'created_by' => $creatorUser->id,
        ]);
        $workspace->users()->attach($creatorUser->id, ['role' => 'creator']);
        $workspace->users()->attach($otherUser->id, ['role' => 'editor']);

        $status = TaskStatus::create(['name' => 'Todo', 'stage' => 'pending', 'order' => 1]);
        $taskType = TaskType::create(['name' => 'Task', 'type' => 'other']);

        // Task created by creatorUser
        $taskCreatedByCreator = Task::create([
            'created_by'   => $creatorUser->id,
            'project_id'   => $project->id,
            'workspace_id' => $workspace->id,
            'status_id'    => $status->id,
            'task_type_id' => $taskType->id,
            'title'        => 'Task By Creator',
            'position'     => 1,
        ]);

        // Task created by otherUser
        $taskCreatedByOther = Task::create([
            'created_by'   => $otherUser->id,
            'project_id'   => $project->id,
            'workspace_id' => $workspace->id,
            'status_id'    => $status->id,
            'task_type_id' => $taskType->id,
            'title'        => 'Task By Other',
            'position'     => 2,
        ]);

        // When creatorUser requests tasks, they must only see their own created task
        $response = $this->actingAs($creatorUser, 'sanctum')->getJson('/api/tasks');
        $response->assertStatus(200);

        $taskIds = collect($response->json('data'))->pluck('id');
        $this->assertTrue($taskIds->contains($taskCreatedByCreator->id));
        $this->assertFalse($taskIds->contains($taskCreatedByOther->id));
    }
}
