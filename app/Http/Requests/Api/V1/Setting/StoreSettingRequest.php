<?php

namespace App\Http\Requests\Api\V1\Setting;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'unique:settings,name'],
            'type'        => ['required', 'string', 'in:bool,string,num'],
            'default'     => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
