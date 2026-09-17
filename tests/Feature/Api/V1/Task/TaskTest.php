<?php

namespace Tests\Feature\Api\V1\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected Workspace $workspace;
    protected TaskStatus $pendingStatus;
    protected TaskStatus $workingStatus;
    protected TaskStatus $completedStatus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->project = Project::create([
            'name'       => 'SaaS Platform',
            'created_by' => $this->user->id,
        ]);

        $this->workspace = Workspace::create([
            'project_id' => $this->project->id,
            'name'       => 'Sprint Workspace',
            'created_by' => $this->user->id,
        ]);

        $this->pendingStatus = TaskStatus::create([
            'name'  => 'To Do',
            'stage' => 'pending',
            'order' => 1,
        ]);

        $this->workingStatus = TaskStatus::create([
            'name'  => 'In Progress',
            'stage' => 'working',
            'order' => 2,
        ]);

        $this->completedStatus = TaskStatus::create([
            'name'  => 'Done',
            'stage' => 'completed',
            'order' => 3,
        ]);
    }

    public function test_guest_cannot_access_tasks(): void
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);
    }

    public function test_auto_position_assigns_incrementing_order(): void
    {
        // Create first task without explicit position
        $response1 = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', [
                'project_id'   => $this->project->id,
                'workspace_id' => $this->workspace->id,
                'status_id'    => $this->pendingStatus->id,
                'title'        => 'First Task',
                'priority'     => 'Normal',
            ]);

        $response1->assertStatus(201);
        $this->assertEquals(1, $response1->json('data.position'));

        // Create second task without explicit position
        $response2 = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', [
                'project_id'   => $this->project->id,
                'workspace_id' => $this->workspace->id,
                'status_id'    => $this->pendingStatus->id,
                'title'        => 'Second Task',
                'priority'     => 'High',
            ]);

        $response2->assertStatus(201);
        $this->assertEquals(2, $response2->json('data.position'));
    }

    public function test_working_at_timestamp_is_set_when_status_transitions_to_working(): void
    {
        $task = Task::create([
            'created_by'   => $this->user->id,
            'project_id'   => $this->project->id,
            'workspace_id' => $this->workspace->id,
            'status_id'    => $this->pendingStatus->id,
            'title'        => 'Initial Pending Task',
            'position'     => 1,
        ]);

        $this->assertNull($task->working_at);

        // Update status to 'In Progress' (stage = working)
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/tasks/' . $task->id, [
                'status_id' => $this->workingStatus->id,
            ]);

        $response->assertStatus(200);
        $updatedTask = Task::find($task->id);

        $this->assertNotNull($updatedTask->working_at);
        $this->assertNull($updatedTask->completed_at);
    }

    public function test_completed_at_and_actual_minutes_are_calculated_when_stage_becomes_completed(): void
    {
        // Create task with working_at set to 45 minutes ago
        $workingTime = Carbon::now()->subMinutes(45);

        $task = Task::create([
            'created_by'   => $this->user->id,
            'project_id'   => $this->project->id,
            'workspace_id' => $this->workspace->id,
            'status_id'    => $this->workingStatus->id,
            'title'        => 'Task Near Completion',
            'position'     => 1,
            'working_at'   => $workingTime,
        ]);

        // Transition to 'Done' (stage = completed)
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/tasks/' . $task->id, [
                'status_id' => $this->completedStatus->id,
            ]);

        $response->assertStatus(200);
        $updatedTask = Task::find($task->id);

        $this->assertNotNull($updatedTask->completed_at);
        $this->assertNotNull($updatedTask->actual_minutes);
        // Actual minutes should be around 45 minutes
        $this->assertEquals(45, $updatedTask->actual_minutes);
    }

    public function test_task_soft_delete(): void
    {
        $task = Task::create([
            'created_by'   => $this->user->id,
            'project_id'   => $this->project->id,
            'workspace_id' => $this->workspace->id,
            'status_id'    => $this->pendingStatus->id,
            'title'        => 'Task To Soft Delete',
            'position'     => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully.',
            ]);

        // Task should not appear in regular query
        $this->assertNull(Task::find($task->id));

        // But should exist in trashed
        $this->assertNotNull(Task::withTrashed()->find($task->id));
        $this->assertNotNull(Task::withTrashed()->find($task->id)->deleted_at);
    }

    public function test_subtask_creation_with_parent_task_id(): void
    {
        $parentTask = Task::create([
            'created_by'   => $this->user->id,
            'project_id'   => $this->project->id,
            'workspace_id' => $this->workspace->id,
            'status_id'    => $this->pendingStatus->id,
            'title'        => 'Parent Feature Task',
            'position'     => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', [
                'project_id'     => $this->project->id,
                'workspace_id'   => $this->workspace->id,
                'status_id'      => $this->pendingStatus->id,
                'parent_task_id' => $parentTask->id,
                'title'          => 'Subtask 1: Write Unit Tests',
                'priority'       => 'Urgent',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'parent_task_id' => $parentTask->id,
                    'title'          => 'Subtask 1: Write Unit Tests',
                ],
            ]);

        $this->assertEquals(1, $parentTask->subtasks()->count());
    }
}
