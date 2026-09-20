<?php

namespace App\Http\Requests\Api\V1\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $settingId = $this->route('setting') instanceof \App\Models\Setting
            ? $this->route('setting')->id
            : $this->route('setting');

        return [
            'name'        => ['nullable', 'string', 'max:255', Rule::unique('settings', 'name')->ignore($settingId)],
            'type'        => ['nullable', 'string', 'in:bool,string,num'],
            'default'     => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
