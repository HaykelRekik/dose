<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Auth\OTP;

use Illuminate\Foundation\Http\FormRequest;

class RequestOTPRequest extends FormRequest
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
            'phone_number' => [
                'required',
                'phone:SA',
                'exists:users,phone',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.exists' => __('The provided phone number is not found'),
        ];
    }
}
