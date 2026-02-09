<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_ids' => ['required', 'array', 'min:3'],
            'category_ids.*' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_ids.min' => 'You must select at least 3 categories.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
        ];
    }
}
