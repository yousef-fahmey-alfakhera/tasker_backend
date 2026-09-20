<?php

namespace App\Http\Requests\Api\V1\UserSetting;

use Illuminate\Foundation\Http\FormRequest;

class SetThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', 'in:light,dark'],
        ];
    }
}
