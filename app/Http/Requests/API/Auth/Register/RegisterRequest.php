<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Auth\Register;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone_number' => [
                'required',
                'phone:SA',
                'unique:users,phone',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.unique' => __('The provided phone number is already registered.'),
        ];
    }
}
