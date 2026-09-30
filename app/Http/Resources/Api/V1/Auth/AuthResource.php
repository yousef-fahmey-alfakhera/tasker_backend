<?php

namespace App\Http\Resources\Api\V1\Auth;

use App\Http\Resources\Api\V1\Project\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    protected ?string $token;

    public function __construct($resource, ?string $token = null)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'code'       => $this->code,
            'project_id' => $this->project_id,
            'project'    => new ProjectResource($this->whenLoaded('project')),
            'roles'      => $this->roles->pluck('name'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        if ($this->token !== null) {
            $data['token'] = [
                'type'         => 'Bearer',
                'access_token' => $this->token,
            ];
        }

        return $data;
    }
}
