<?php

namespace App\Http\Resources\Api\V1\Task;

use App\Http\Resources\Api\V1\Project\ProjectResource;
use App\Http\Resources\Api\V1\TaskStatus\TaskStatusResource;
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
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'description'    => $this->description,
            'priority'       => $this->priority,
            'position'       => $this->position,
            'parent_task_id' => $this->parent_task_id,
            'created_by'     => $this->created_by,
            'creator'        => $this->whenLoaded('creator', function () {
                return [
                    'id'    => $this->creator->id,
                    'name'  => $this->creator->name,
                    'email' => $this->creator->email,
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
            'start_date'     => $this->start_date?->toISOString(),
            'due_date'       => $this->due_date?->toISOString(),
            'working_at'     => $this->working_at?->toISOString(),
            'completed_at'   => $this->completed_at?->toISOString(),
            'actual_minutes' => $this->actual_minutes,
            'subtasks_count' => $this->whenCounted('subtasks'),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
            'deleted_at'     => $this->deleted_at?->toISOString(),
        ];
    }
}
