<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    // Channel: private-chat.{chatId}
    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->message->chat_id)];
    }

    public function broadcastAs(): string
    {
        return 'App\\Events\\MessageSent';   // harus sama dengan ReverbService
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id'             => $this->message->id,
                'chat_id'        => $this->message->chat_id,
                'sender_id'      => $this->message->sender_id,
                'is_mine'        => false,   // selalu false untuk penerima
                'message'        => $this->message->message,
                'attachment_url' => $this->message->attachment_url,
                'type'           => $this->message->type,
                'is_read'        => false,
                'created_at'     => $this->message->created_at->toIso8601String(),
            ],
        ];
    }
}
