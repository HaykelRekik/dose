<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Auth\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'phone_number' => [
                'required',
                'phone:SA',
                Rule::unique('users', 'phone')->ignore($this->user()->id),
            ],
        ];
    }
}
