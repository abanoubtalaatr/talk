<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentToNetwork implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  int  $targetUserId  User to notify (receives event once on their private channel)
     */
    public function __construct(
        public Message $message,
        public int $targetUserId
    ) {
        $this->message->load(['sender', 'receiver']);
    }

    /**
     * Broadcast to a single user's channel so each user receives the notification exactly once.
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
        return 'message.sent_to_network';
    }

    /**
     * Data to broadcast (light payload for "new message in your network").
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'sender_username' => $this->message->sender->username,
            'receiver_username' => $this->message->receiver->username,
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
