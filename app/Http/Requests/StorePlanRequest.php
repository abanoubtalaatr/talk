<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_free' => ['nullable', 'boolean'],
            'new_chats_per_day' => ['nullable', 'integer', 'min:0'],
            'messages_per_day' => ['nullable', 'integer', 'min:0'],
            'send_new_msg_per_day' => ['nullable', 'integer', 'min:0'],
            'posts_per_day' => ['nullable', 'integer', 'min:-1'],
            'post_chars' => ['nullable', 'integer', 'min:1'],
            'message_chars' => ['nullable', 'integer', 'min:1'],
            'topic_change_days' => ['nullable', 'integer', 'min:0'],
            'topics_count' => ['nullable', 'integer', 'min:0'],
            'open_chats' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
