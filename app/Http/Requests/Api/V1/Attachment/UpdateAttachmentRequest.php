<?php

namespace App\Http\Requests\Api\V1\Attachment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file'            => ['nullable', 'file', 'max:51200'],
            'attachable_type' => ['nullable', 'string', 'max:255'],
            'attachable_id'   => ['nullable', 'integer'],
        ];
    }
}
