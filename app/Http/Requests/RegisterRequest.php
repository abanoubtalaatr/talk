<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:30', 'unique:users,username', 'alpha_dash'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'referral_username' => ['nullable', 'string', 'exists:users,username'],
            'mac_address' => ['nullable', 'string', 'max:17'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'This username is already taken.',
            'referral_username.exists' => 'The referral user does not exist.',
        ];
    }
}
