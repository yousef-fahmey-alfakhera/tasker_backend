<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\TaskStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    /**
     * Get all tasks with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Task::with(['creator', 'fixedBy', 'project', 'workspace', 'status'])
            ->withCount('subtasks')
            ->orderBy('position');

        if (!empty($filters['workspace_id'])) {
            $query->where('workspace_id', $filters['workspace_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->get();
    }

    /**
     * Get task by instance.
     */
    public function getById(Task $task): Task
    {
        return $task->loadMissing(['creator', 'fixedBy', 'project', 'workspace', 'status'])
            ->loadCount('subtasks');
    }

    /**
     * Create a task with auto-position and stage tracking.
     */
    public function create(array $data, int $userId): Task
    {
        $data['created_by'] = $userId;

        // Auto position: max(position) + 1 in the workspace
        if (!isset($data['position']) || $data['position'] === null) {
            $maxPosition = Task::where('workspace_id', $data['workspace_id'])->max('position') ?? 0;
            $data['position'] = $maxPosition + 1;
        }

        // Apply status stage logic
        if (!empty($data['status_id'])) {
            $status = TaskStatus::find($data['status_id']);
            if ($status) {
                if ($status->stage === 'working' && empty($data['working_at'])) {
                    $data['working_at'] = now();
                } elseif ($status->stage === 'completed') {
                    $completedAt = !empty($data['completed_at']) ? Carbon::parse($data['completed_at']) : now();
                    $data['completed_at'] = $completedAt;

                    $workingAt = !empty($data['working_at'])
                        ? Carbon::parse($data['working_at'])
                        : now();

                    $data['actual_minutes'] = (int) max(0, $workingAt->diffInMinutes($completedAt));
                }
            }
        }

        $task = Task::create($data);

        return $task->loadMissing(['creator', 'fixedBy', 'project', 'workspace', 'status']);
    }

    /**
     * Update task attributes, handling stage transitions and actual_minutes.
     */
    public function update(Task $task, array $data): Task
    {
        if (isset($data['status_id']) && $data['status_id'] !== $task->status_id) {
            $status = TaskStatus::find($data['status_id']);
            if ($status) {
                if ($status->stage === 'working') {
                    if (empty($task->working_at) && empty($data['working_at'])) {
                        $data['working_at'] = now();
                    }
                } elseif ($status->stage === 'completed') {
                    $completedAt = !empty($data['completed_at']) ? Carbon::parse($data['completed_at']) : now();
                    $data['completed_at'] = $completedAt;

                    $workingAt = !empty($data['working_at'])
                        ? Carbon::parse($data['working_at'])
                        : ($task->working_at ?? $task->created_at ?? now());

                    $data['actual_minutes'] = (int) max(0, $workingAt->diffInMinutes($completedAt));
                }
            }
        }

        $task->update($data);

        return $task->fresh(['creator', 'fixedBy', 'project', 'workspace', 'status'])
            ->loadCount('subtasks');
    }

    /**
     * Soft delete a task.
     */
    public function delete(Task $task): bool
    {
        return (bool) $task->delete();
    }
}
