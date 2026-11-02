<?php

namespace App\Http\Resources\Api\V1\UserType;

use App\Http\Resources\Api\V1\TaskType\TaskTypeResource;
use App\Models\TaskType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rawResp = (array) ($this->respnsapity ?? []);
        $respIds = array_values(array_map(
            fn($val) => is_numeric($val) ? (int) $val : $val,
            $rawResp
        ));

        $numericIds = array_values(array_filter($respIds, 'is_int'));

        return [
            'id'                     => $this->id,
            'user_id'                => $this->user_id,
            'user'                   => $this->whenLoaded('user', function () {
                return [
                    'id'         => $this->user->id,
                    'name'       => $this->user->name,
                    'email'      => $this->user->email,
                    'project_id' => $this->user->project_id,
                ];
            }),
            'type'                   => $this->type,
            'respnsapity'            => $respIds,
            'responsible_task_types' => !empty($numericIds)
                ? TaskTypeResource::collection(TaskType::whereIn('id', $numericIds)->get())
                : [],
            'created_at'             => $this->created_at?->toISOString(),
            'updated_at'             => $this->updated_at?->toISOString(),
        ];
    }
}

