<?php

namespace App\Http\Requests\Api\V1\UserSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => ['required'],
        ];
    }
}
