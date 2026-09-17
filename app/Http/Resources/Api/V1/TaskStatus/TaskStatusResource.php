<?php

namespace App\Http\Resources\Api\V1\TaskStatus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'stage'        => $this->stage,
            'task_id'      => $this->task_id,
            'project_id'   => $this->project_id,
            'workspace_id' => $this->workspace_id,
            'color'        => $this->color,
            'order'        => $this->order,
            'tasks_count'  => $this->whenCounted('tasks'),
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}
