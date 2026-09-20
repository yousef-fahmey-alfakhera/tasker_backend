<?php

namespace App\Http\Requests\Api\V1\Attachment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file'            => ['required', 'file', 'max:51200'],
            'attachable_type' => ['required', 'string', 'max:255'],
            'attachable_id'   => ['required', 'integer'],
        ];
    }
}
