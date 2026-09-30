<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required_without:code', 'nullable', 'string'],
            'code'     => ['required_without:email', 'nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
