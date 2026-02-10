<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_free' => $this->is_free,
            'new_chats_per_day' => $this->new_chats_per_day,
            'messages_per_day' => $this->messages_per_day,
            'send_new_msg_per_day' => $this->send_new_msg_per_day,
            'posts_per_day' => $this->posts_per_day,
            'post_chars' => $this->post_chars,
            'message_chars' => $this->message_chars,
            'topic_change_days' => $this->topic_change_days,
            'topics_count' => $this->topics_count,
            'open_chats' => $this->open_chats,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
