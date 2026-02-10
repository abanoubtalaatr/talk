<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  int  $targetUserId  User to notify (receives event once on their private channel)
     */
    public function __construct(
        public Post $post,
        public int $targetUserId
    ) {
        $this->post->load(['user', 'categories'])
            ->loadCount(['likes', 'stars', 'reactions', 'comments']);
    }

    /**
     * Broadcast to a single user's channel so each user receives the post exactly once.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->targetUserId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'post.created';
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith(): array
    {
        $post = $this->post;

        return [
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'is_anonymous' => $post->is_anonymous,
                'user' => $post->is_anonymous ? null : [
                    'id' => $post->user->id,
                    'username' => $post->user->username,
                ],
                'categories' => $post->categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                ])->all(),
                'likes_count' => $post->likes_count ?? 0,
                'stars_count' => $post->stars_count ?? 0,
                'reactions_count' => $post->reactions_count ?? 0,
                'comments_count' => $post->comments_count ?? 0,
                'created_at' => $post->created_at->toISOString(),
            ],
        ];
    }
}
