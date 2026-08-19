<?php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class SuppConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $userId,
        public array $payload
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('supp.users.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'supp.conversation.updated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
