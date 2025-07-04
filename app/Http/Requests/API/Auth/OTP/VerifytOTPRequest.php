<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Auth\OTP;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifytOTPRequest extends FormRequest
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
            'otp' => [
                'required',
                'numeric',
                'digits:6',
            ],
        ];
    }

    /**
     * Format the errors from the given Validator instance to keep the response consistent.
     *
     * @throws HttpResponseException
     */
    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->error(
                message: __('The given data is invalid.'),
                data: $validator->errors()->toArray(),
                code: 422
            )
        );
    }

    public function messages(): array
    {
        return [
            'phone_number.exists' => __('The provided phone number is not found'),
        ];
    }
}
