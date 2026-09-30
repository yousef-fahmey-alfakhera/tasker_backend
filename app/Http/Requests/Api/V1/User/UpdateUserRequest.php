<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') instanceof \App\Models\User
            ? $this->route('user')->id
            : $this->route('user');

        return [
            'name'       => ['sometimes', 'required', 'string', 'max:255'],
            'email'      => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password'   => ['nullable', 'string', 'min:8'],
            'code'       => ['nullable', 'string', 'max:50', Rule::unique('users', 'code')->ignore($userId)],
            'project_id' => ['sometimes', 'required', 'exists:projects,id'],
            'role'       => ['nullable', 'string', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'برجاء اختيار القسم',
            'project_id.exists'   => 'برجاء اختيار القسم',
        ];
    }
}
