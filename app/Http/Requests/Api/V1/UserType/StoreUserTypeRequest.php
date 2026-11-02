<?php

namespace App\Http\Requests\Api\V1\UserType;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('respnsapity') && is_array($this->respnsapity)) {
            $this->merge([
                'respnsapity' => array_values(array_map(
                    fn($id) => is_numeric($id) ? (int) $id : $id,
                    $this->respnsapity
                )),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['required', 'integer', 'exists:users,id', 'unique:user_types,user_id'],
            'type'          => ['required', 'string', 'max:255'],
            'respnsapity'   => ['nullable', 'array'],
            'respnsapity.*' => ['nullable'],
        ];
    }
}
