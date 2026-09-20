<?php

namespace Tests\Feature\Api\V1\TaskStatus;

use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_guest_cannot_access_task_statuses(): void
    {
        $response = $this->getJson('/api/task-statuses');
        $response->assertStatus(401);
    }

    public function test_user_can_create_task_status_with_stage(): void
    {
        $payload = [
            'name'  => 'In Development',
            'stage' => 'working',
            'color' => '#3b82f6',
            'order' => 2,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/task-statuses', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Task status created successfully.',
                'data'    => [
                    'name'  => 'In Development',
                    'stage' => 'working',
                    'color' => '#3b82f6',
                ],
            ]);

        $this->assertDatabaseHas('task_statuses', [
            'name'  => 'In Development',
            'stage' => 'working',
        ]);
    }

    public function test_stage_validation_fails_for_invalid_stage(): void
    {
        $payload = [
            'name'  => 'Invalid Status',
            'stage' => 'unknown_stage',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/task-statuses', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stage']);
    }

    public function test_user_can_show_and_update_task_status(): void
    {
        $status = TaskStatus::create([
            'name'  => 'Review Pending',
            'stage' => 'pending',
        ]);

        $getResponse = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/task-statuses/' . $status->id);

        $getResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => ['name' => 'Review Pending'],
            ]);

        $putResponse = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/task-statuses/' . $status->id, [
                'name'  => 'Finished & Approved',
                'stage' => 'completed',
            ]);

        $putResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'name'  => 'Finished & Approved',
                    'stage' => 'completed',
                ],
            ]);
    }

    public function test_user_can_delete_task_status(): void
    {
        $status = TaskStatus::create([
            'name'  => 'To Be Dropped',
            'stage' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/task-statuses/' . $status->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task status deleted successfully.',
            ]);

        $this->assertDatabaseMissing('task_statuses', ['id' => $status->id]);
    }
}
