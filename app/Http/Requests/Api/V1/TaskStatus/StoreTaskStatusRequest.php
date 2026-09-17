<?php

namespace App\Http\Requests\Api\V1\TaskStatus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'stage'        => ['required', 'string', Rule::in(['pending', 'working', 'completed'])],
            'task_id'      => ['nullable', 'integer'],
            'project_id'   => ['nullable', 'integer', 'exists:projects,id'],
            'workspace_id' => ['nullable', 'integer', 'exists:workspaces,id'],
            'color'        => ['nullable', 'string', 'max:50'],
            'order'        => ['nullable', 'integer'],
        ];
    }
}
