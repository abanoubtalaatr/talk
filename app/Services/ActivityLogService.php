<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogService
{
    /**
     * Log a user activity.
     */
    public function log(User $user, string $type, string $description, ?Request $request = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $user->id,
            'type' => $type,
            'description' => $description,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * Log a registration activity.
     */
    public function logRegistration(User $user, Request $request): ActivityLog
    {
        return $this->log(
            $user,
            ActivityLog::TYPE_REGISTRATION,
            "User {$user->username} registered.",
            $request,
        );
    }

    /**
     * Log a login activity.
     */
    public function logLogin(User $user, Request $request): ActivityLog
    {
        return $this->log(
            $user,
            ActivityLog::TYPE_LOGIN,
            "User {$user->username} logged in.",
            $request,
        );
    }

    /**
     * Log a post creation activity.
     */
    public function logPostCreated(User $user, Request $request): ActivityLog
    {
        return $this->log(
            $user,
            ActivityLog::TYPE_POST_CREATED,
            "User {$user->username} created a post.",
            $request,
        );
    }

    /**
     * Log a comment creation activity.
     */
    public function logCommentCreated(User $user, Request $request): ActivityLog
    {
        return $this->log(
            $user,
            ActivityLog::TYPE_COMMENT_CREATED,
            "User {$user->username} added a comment.",
            $request,
        );
    }

    /**
     * Log a reaction activity.
     */
    public function logReactionAdded(User $user, string $reactionType, Request $request): ActivityLog
    {
        return $this->log(
            $user,
            ActivityLog::TYPE_REACTION_ADDED,
            "User {$user->username} added a {$reactionType} reaction.",
            $request,
        );
    }

    /**
     * Log a message sent activity.
     */
    public function logMessageSent(User $user, Request $request): ActivityLog
    {
        return $this->log(
            $user,
            ActivityLog::TYPE_MESSAGE_SENT,
            "User {$user->username} sent a message.",
            $request,
        );
    }

    /**
     * Log a profile update activity.
     */
    public function logProfileUpdated(User $user, Request $request): ActivityLog
    {
        return $this->log(
            $user,
            ActivityLog::TYPE_PROFILE_UPDATED,
            "User {$user->username} updated their profile.",
            $request,
        );
    }

    /**
     * Log a categories update activity.
     */
    public function logCategoriesUpdated(User $user, Request $request): ActivityLog
    {
        return $this->log(
            $user,
            ActivityLog::TYPE_CATEGORIES_UPDATED,
            "User {$user->username} updated their preferred categories.",
            $request,
        );
    }
}
