<?php

namespace App\Services\TaskType;

use App\Models\TaskType;
use Illuminate\Database\Eloquent\Collection;

class TaskTypeService
{
    /**
     * Get all task types.
     */
    public function getAll(): Collection
    {
        return TaskType::latest()->get();
    }

    /**
     * Get task type by instance.
     */
    public function getById(TaskType $taskType): TaskType
    {
        return $taskType;
    }

    /**
     * Create a task type.
     */
    public function create(array $data): TaskType
    {
        return TaskType::create($data);
    }

    /**
     * Update task type.
     */
    public function update(TaskType $taskType, array $data): TaskType
    {
        $taskType->update($data);

        return $taskType->fresh();
    }

    /**
     * Delete task type.
     */
    public function delete(TaskType $taskType): bool
    {
        return (bool) $taskType->delete();
    }
}
