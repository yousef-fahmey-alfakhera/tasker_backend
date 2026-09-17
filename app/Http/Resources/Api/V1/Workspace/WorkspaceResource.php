<?php

namespace App\Http\Resources\Api\V1\Workspace;

use App\Http\Resources\Api\V1\Project\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkspaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'project_id'  => $this->project_id,
            'project'     => new ProjectResource($this->whenLoaded('project')),
            'name'        => $this->name,
            'description' => $this->description,
            'created_by'  => $this->created_by,
            'creator'     => $this->whenLoaded('creator', function () {
                return [
                    'id'    => $this->creator->id,
                    'name'  => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'users'       => $this->whenLoaded('users', function () {
                return $this->users->map(function ($user) {
                    return [
                        'id'    => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                        'role'  => $user->pivot->role,
                    ];
                });
            }),
            'tasks_count' => $this->whenCounted('tasks'),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
