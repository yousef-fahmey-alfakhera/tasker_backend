<?php

namespace App\Http\Resources\Api\V1\UserSetting;

use App\Http\Resources\Api\V1\Setting\SettingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingResource extends JsonResource
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
            'user_id'      => $this->user_id,
            'setting_id'   => $this->setting_id,
            'setting_name' => $this->whenLoaded('setting', fn () => $this->setting->name),
            'type'         => $this->whenLoaded('setting', fn () => $this->setting->type),
            'value'        => $this->value,
            'casted_value' => $this->casted_value,
            'setting'      => new SettingResource($this->whenLoaded('setting')),
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}
