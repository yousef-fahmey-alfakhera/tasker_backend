<?php

namespace App\Http\Requests\Api\V1\UserType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userTypeId = $this->route('userType')?->id ?? $this->route('userType');

        return [
            'user_id'       => [
                'sometimes',
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('user_types', 'user_id')->ignore($userTypeId),
            ],
            'type'          => ['sometimes', 'required', 'string', 'max:255'],
            'respnsapity'   => ['nullable', 'array'],
            'respnsapity.*' => ['string', 'max:255'],
        ];
    }
}
