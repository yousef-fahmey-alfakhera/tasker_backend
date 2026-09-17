<?php

namespace App\Http\Requests\Api\V1\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'       => ['sometimes', 'required', 'integer', 'exists:projects,id'],
            'name'             => ['sometimes', 'required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'users'            => ['nullable', 'array'],
            'users.*.user_id'  => ['required_with:users', 'integer', 'exists:users,id'],
            'users.*.role'     => ['required_with:users', 'string', Rule::in(['viewer', 'admin', 'it', 'creator', 'editor'])],
        ];
    }
}
