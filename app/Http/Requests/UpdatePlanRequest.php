<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'is_free' => ['sometimes', 'boolean'],
            'new_chats_per_day' => ['sometimes', 'integer', 'min:0'],
            'messages_per_day' => ['sometimes', 'integer', 'min:0'],
            'send_new_msg_per_day' => ['sometimes', 'integer', 'min:0'],
            'posts_per_day' => ['sometimes', 'integer', 'min:-1'],
            'post_chars' => ['sometimes', 'integer', 'min:1'],
            'message_chars' => ['sometimes', 'integer', 'min:1'],
            'topic_change_days' => ['sometimes', 'integer', 'min:0'],
            'topics_count' => ['sometimes', 'integer', 'min:0'],
            'open_chats' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
