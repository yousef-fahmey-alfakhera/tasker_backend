<?php

namespace App\Http\Requests\Api\V1\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'      => ['required', 'integer', 'exists:projects,id'],
            'workspace_id'    => ['required', 'integer', 'exists:workspaces,id'],
            'status_id'       => ['required', 'integer', 'exists:task_statuses,id'],
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'priority'        => ['nullable', 'string', Rule::in(['None', 'Low', 'Normal', 'High', 'Urgent'])],
            'fixed_by'        => ['nullable', 'integer', 'exists:users,id'],
            'parent_task_id'  => ['nullable', 'integer', 'exists:tasks,id'],
            'start_date'      => ['nullable', 'date'],
            'due_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'position'        => ['nullable', 'integer'],
        ];
    }
}
