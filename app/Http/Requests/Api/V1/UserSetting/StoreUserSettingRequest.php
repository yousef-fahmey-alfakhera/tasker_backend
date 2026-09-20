<?php

namespace App\Http\Requests\Api\V1\UserSetting;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'setting_id' => ['required', 'integer', 'exists:settings,id'],
            'value'      => ['required'],
            'user_id'    => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
