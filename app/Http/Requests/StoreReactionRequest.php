<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:like,star'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Reaction type must be either "like" or "star".',
        ];
    }
}
