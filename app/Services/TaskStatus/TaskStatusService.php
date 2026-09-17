<?php

namespace App\Services\TaskStatus;

use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Collection;

class TaskStatusService
{
    /**
     * Get all task statuses.
     */
    public function getAll(): Collection
    {
        return TaskStatus::withCount('tasks')->orderBy('order')->latest()->get();
    }

    /**
     * Get task status by instance.
     */
    public function getById(TaskStatus $taskStatus): TaskStatus
    {
        return $taskStatus->loadCount('tasks');
    }

    /**
     * Create a task status.
     */
    public function create(array $data): TaskStatus
    {
        return TaskStatus::create($data);
    }

    /**
     * Update a task status.
     */
    public function update(TaskStatus $taskStatus, array $data): TaskStatus
    {
        $taskStatus->update($data);

        return $taskStatus->fresh()->loadCount('tasks');
    }

    /**
     * Delete a task status.
     */
    public function delete(TaskStatus $taskStatus): bool
    {
        return (bool) $taskStatus->delete();
    }
}
