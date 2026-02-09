<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'description',
        'ip',
        'user_agent',
    ];

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    public const TYPE_REGISTRATION = 'registration';
    public const TYPE_LOGIN = 'login';
    public const TYPE_POST_CREATED = 'post_created';
    public const TYPE_COMMENT_CREATED = 'comment_created';
    public const TYPE_REACTION_ADDED = 'reaction_added';
    public const TYPE_MESSAGE_SENT = 'message_sent';
    public const TYPE_PROFILE_UPDATED = 'profile_updated';
    public const TYPE_CATEGORIES_UPDATED = 'categories_updated';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
