<?php

namespace App\Http\Resources\Api\V1\User;

use App\Http\Resources\Api\V1\Project\ProjectResource;
use App\Http\Resources\Api\V1\UserType\UserTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'email'               => $this->email,
            'code'                => $this->code,
            'project_id'          => $this->project_id,
            'project'             => new ProjectResource($this->whenLoaded('project')),
            'user_type'           => new UserTypeResource($this->whenLoaded('userType')),
            'roles'               => $this->relationLoaded('roles') ? $this->roles->pluck('name') : [],
            'created_tasks_count' => $this->whenCounted('createdTasks'),
            'fixed_tasks_count'   => $this->whenCounted('fixedTasks'),
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
            'deleted_at'          => $this->deleted_at?->toISOString(),
        ];
    }
}
