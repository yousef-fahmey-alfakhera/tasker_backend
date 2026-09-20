<?php

namespace App\Http\Resources\Api\V1\Attachment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'attachable_type' => $this->attachable_type,
            'attachable_id'   => $this->attachable_id,
            'file'            => $this->file,
            'path'            => $this->path,
            'file_path'       => $this->file_path ?? $this->getFilePath(),
            'type'            => $this->type,
            'size'            => $this->size,
            'created_by'      => $this->created_by,
            'creator'         => $this->whenLoaded('creator', function () {
                return [
                    'id'    => $this->creator->id,
                    'name'  => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}
