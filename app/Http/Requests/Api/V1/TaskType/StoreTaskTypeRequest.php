<?php

namespace App\Http\Requests\Api\V1\TaskType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('type') && $this->filled('name')) {
            $this->merge([
                'type' => \Illuminate\Support\Str::slug($this->input('name'), '_') ?: 'other',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'type'       => ['nullable', 'string', 'max:100'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ];
    }
}
