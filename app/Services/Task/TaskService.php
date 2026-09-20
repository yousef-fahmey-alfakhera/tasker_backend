<?php

namespace App\Services\Task;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\File\FileService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class TaskService
{
    public function __construct(
        protected FileService $fileService = new FileService()
    ) {}

    /**
     * Get all tasks with optional filters.
     */
    public function getAll(array $filters = [], ?User $user = null): Collection
    {
        $query = Task::with(['creator', 'fixedBy', 'project', 'workspace', 'status', 'taskType', 'attachments'])
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

        // If user is provided and has NO userType, filter tasks created by user
        if ($user) {
            $user->loadMissing('userType');
            if (! $user->userType) {
                $query->where('created_by', $user->id);
            }
        }

        return $query->get();
    }

    /**
     * Get task by instance.
     */
    public function getById(Task $task): Task
    {
        return $task->loadMissing(['creator', 'fixedBy', 'project', 'workspace', 'status', 'taskType', 'attachments'])
            ->loadCount('subtasks');
    }

    /**
     * Create a task with auto-position, stage tracking, and optional attachments.
     */
    public function create(array $data, int $userId): Task
    {
        $data['created_by'] = $userId;

        // Extract attachments before creating task model
        $attachments = $data['attachments'] ?? [];
        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $attachments[] = $data['attachment'];
        }
        unset($data['attachments'], $data['attachment']);

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

        // Store attachments using Task::ATTACHMENT_PATH if provided
        if (!empty($attachments)) {
            $this->storeAttachments($task, $attachments, $userId);
        }

        return $task->loadMissing(['creator', 'fixedBy', 'project', 'workspace', 'status', 'taskType', 'attachments']);
    }

    /**
     * Store attachments for a task using Task::ATTACHMENT_PATH.
     */
    public function storeAttachments(Task $task, array|UploadedFile $files, int $userId): Collection
    {
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        $created = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $storedPath = $this->fileService->upload($file, Task::ATTACHMENT_PATH);
                $created[] = $task->attachments()->create([
                    'file'       => $file->getClientOriginalName(),
                    'path'       => $storedPath,
                    'type'       => $file->getClientMimeType() ?: ($file->getClientOriginalExtension() ?: 'unknown'),
                    'size'       => $file->getSize() ?: 0,
                    'created_by' => $userId,
                ]);
            }
        }

        return new Collection($created);
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

        return $task->fresh(['creator', 'fixedBy', 'project', 'workspace', 'status', 'taskType', 'attachments'])
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
