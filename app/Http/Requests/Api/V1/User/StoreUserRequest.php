<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8'],
            'code'       => ['nullable', 'string', 'max:50', 'unique:users,code'],
            'project_id' => ['required', 'exists:projects,id'],
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
