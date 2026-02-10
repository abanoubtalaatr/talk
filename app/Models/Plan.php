<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'is_free',
        'new_chats_per_day',
        'messages_per_day',
        'send_new_msg_per_day',
        'posts_per_day',
        'post_chars',
        'message_chars',
        'topic_change_days',
        'topics_count',
        'open_chats',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'new_chats_per_day' => 'integer',
            'messages_per_day' => 'integer',
            'send_new_msg_per_day' => 'integer',
            'posts_per_day' => 'integer',
            'post_chars' => 'integer',
            'message_chars' => 'integer',
            'topic_change_days' => 'integer',
            'topics_count' => 'integer',
            'open_chats' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
