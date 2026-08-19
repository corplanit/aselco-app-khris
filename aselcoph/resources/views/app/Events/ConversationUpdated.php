<?php

namespace App\Events;

use App\Models\Chats\Conversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Conversation $conversation;
    public int $forUserId;
    public int $unreadCount;

    public function __construct(Conversation $conversation, int $forUserId, int $unreadCount)
    {
        $conversation->loadMissing([
            'participants.user',
            'messages' => function ($q) {
                $q->latest()->limit(1);
            },
        ]);

        $this->conversation = $conversation;
        $this->forUserId    = $forUserId;
        $this->unreadCount  = $unreadCount;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('users.' . $this->forUserId)];
    }

    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    public function broadcastWith(): array
    {
        $isGroup = $this->conversation->type === 'group';

        if ($isGroup) {
            $displayTitle = $this->conversation->name ?? 'Group Chat';
        } else {
            $other = $this->conversation->participants
                ->firstWhere('user_id', '!=', $this->forUserId);

            $displayTitle = optional(optional($other)->user)->name ?? 'Direct Message';
        }

        $latest = $this->conversation->messages()->latest()->first();
        $lastAt = optional($latest?->created_at ?? $this->conversation->updated_at)->toIso8601String();

        return [
            'conversation_id' => $this->conversation->id,
            'title'           => $displayTitle,
            'is_group'        => $isGroup,
            'last_message'    => $latest?->body,
            'last_at'         => $lastAt,
            'unread_count'    => $this->unreadCount,
        ];
    }
}
