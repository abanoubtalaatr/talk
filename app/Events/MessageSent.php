<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message
    ) {
        $this->message->load(['sender', 'receiver']);
    }

    /**
     * Get the channels the event should broadcast on.
     * Receiver gets the full message in real-time.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->message->receiver_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'sender_id' => $this->message->sender_id,
                'receiver_id' => $this->message->receiver_id,
                'sender' => [
                    'id' => $this->message->sender->id,
                    'username' => $this->message->sender->username,
                ],
                'receiver' => [
                    'id' => $this->message->receiver->id,
                    'username' => $this->message->receiver->username,
                ],
                'created_at' => $this->message->created_at->toISOString(),
            ],
        ];
    }
}
