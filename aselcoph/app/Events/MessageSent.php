<?php

namespace App\Events;

use App\Models\Chats\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public array $users;

    public function __construct(Message $message, array $users)
    {
        // Eager-load what the UI needs
        $this->message = $message->load('user', 'attachments', 'reactions');
        $this->users   = $users;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversations.' . $this->message->conversation_id),
        ];

        foreach ($this->users as $id) {
            $channels[] = new PrivateChannel('users.' . $id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id'              => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'body'            => $this->message->body,
                'created_at'      => $this->message->created_at->toIso8601String(),
                'edited_at'       => optional($this->message->edited_at)->toIso8601String(),
                'user' => [
                    'id'     => $this->message->user->id,
                    'name'   => $this->message->user->name,
                    'avatar' => $this->message->user->profile_photo_url ?? null,
                ],
            ],
        ];
    }
}
