<?php

namespace App\Http\Resources\Api\V1\Task;

use App\Http\Resources\Api\V1\Attachment\AttachmentResource;
use App\Http\Resources\Api\V1\Project\ProjectResource;
use App\Http\Resources\Api\V1\TaskStatus\TaskStatusResource;
use App\Http\Resources\Api\V1\TaskType\TaskTypeResource;
use App\Http\Resources\Api\V1\Workspace\WorkspaceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isResponsible = 0;

        if ($user) {
            $userType = $user->relationLoaded('userType') ? $user->userType : $user->userType()->first();
            if ($userType) {
                $taskTypeId = (int) $this->task_type_id;
                $rawResponsibilities = (array) ($userType->respnsapity ?? []);

                // 1. Primary match: task_type_id is in user's responsibilities list of IDs
                $intResponsibilities = array_filter(
                    array_map(fn($r) => is_numeric($r) ? (int) $r : null, $rawResponsibilities)
                );

                if ($taskTypeId && in_array($taskTypeId, $intResponsibilities, true)) {
                    $isResponsible = 1;
                } else {
                    // 2. Fallback matching by type/name for legacy support
                    $taskTypeModel = $this->relationLoaded('taskType') ? $this->taskType : $this->taskType;
                    if ($taskTypeModel) {
                        $taskType = strtolower(trim((string) $taskTypeModel->type));
                        $taskName = strtolower(trim((string) $taskTypeModel->name));
                        $uType = strtolower(trim((string) $userType->type));
                        $strResponsibilities = array_map(
                            fn($r) => strtolower(trim((string) $r)),
                            $rawResponsibilities
                        );

                        if (
                            $uType === $taskType ||
                            $uType === $taskName ||
                            in_array($taskType, $strResponsibilities, true) ||
                            in_array($taskName, $strResponsibilities, true)
                        ) {
                            $isResponsible = 1;
                        }
                    }
                }
            }
        }

        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'description'    => $this->description,
            'priority'       => $this->priority,
            'position'       => $this->position,
            'parent_task_id' => $this->parent_task_id,
            'created_by'     => $this->created_by,
            'creator'        => $this->whenLoaded('creator', function () {
                if (! $this->creator) {
                    return null;
                }

                $creatorProject = $this->creator->relationLoaded('project')
                    ? $this->creator->project
                    : $this->creator->project;

                return [
                    'id'           => $this->creator->id,
                    'name'         => $this->creator->name,
                    'email'        => $this->creator->email,
                    'project_id'   => $this->creator->project_id,
                    'project_name' => $creatorProject?->name,
                    'department'   => $creatorProject?->name,
                ];
            }),
            'fixed_by'       => $this->fixed_by,
            'fixed_by_user'  => $this->whenLoaded('fixedBy', function () {
                return [
                    'id'    => $this->fixedBy->id,
                    'name'  => $this->fixedBy->name,
                    'email' => $this->fixedBy->email,
                ];
            }),
            'project_id'     => $this->project_id,
            'project'        => new ProjectResource($this->whenLoaded('project')),
            'workspace_id'   => $this->workspace_id,
            'workspace'      => new WorkspaceResource($this->whenLoaded('workspace')),
            'status_id'      => $this->status_id,
            'status'         => new TaskStatusResource($this->whenLoaded('status')),
            'task_type_id'   => $this->task_type_id,
            'task_type'      => new TaskTypeResource($this->whenLoaded('taskType')),
            'respnsapity'    => $isResponsible,
            'start_date'     => $this->start_date?->toISOString(),
            'due_date'       => $this->due_date?->toISOString(),
            'working_at'     => $this->working_at?->toISOString(),
            'completed_at'   => $this->completed_at?->toISOString(),
            'actual_minutes' => $this->actual_minutes,
            'subtasks_count' => $this->whenCounted('subtasks'),
            'attachments'    => AttachmentResource::collection($this->whenLoaded('attachments')),
            'children'       => TaskResource::collection($this->whenLoaded('children')),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
            'deleted_at'     => $this->deleted_at?->toISOString(),
        ];
    }
}
