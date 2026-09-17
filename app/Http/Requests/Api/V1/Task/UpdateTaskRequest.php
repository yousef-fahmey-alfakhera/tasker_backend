<?php

namespace App\Http\Requests\Api\V1\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'      => ['sometimes', 'required', 'integer', 'exists:projects,id'],
            'workspace_id'    => ['sometimes', 'required', 'integer', 'exists:workspaces,id'],
            'status_id'       => ['sometimes', 'required', 'integer', 'exists:task_statuses,id'],
            'title'           => ['sometimes', 'required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'priority'        => ['sometimes', 'string', Rule::in(['None', 'Low', 'Normal', 'High', 'Urgent'])],
            'fixed_by'        => ['nullable', 'integer', 'exists:users,id'],
            'parent_task_id'  => ['nullable', 'integer', 'exists:tasks,id'],
            'start_date'      => ['nullable', 'date'],
            'due_date'        => ['nullable', 'date'],
            'position'        => ['nullable', 'integer'],
        ];
    }
}
