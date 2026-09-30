<?php

namespace App\Http\Resources\Api\V1\Profile;

use App\Http\Resources\Api\V1\Project\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'code'              => $this->code,
            'project_id'        => $this->project_id,
            'project'           => new ProjectResource($this->whenLoaded('project')),
            'roles'             => $this->roles->pluck('name'),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
