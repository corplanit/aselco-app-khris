<?php
namespace App\Events;

use App\Models\SuppMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SuppMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SuppMessage $message) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('supp.conversations.' . $this->message->conversation_id);
    }

    public function broadcastAs(): string
    {
        return 'supp.message.sent';
    }

    public function broadcastWith(): array
    {
        $m = $this->message->load(['user','attachments']);
        return [
            'message' => [
                'id' => $m->id,
                'conversation_id' => $m->conversation_id,
                'type' => $m->type,
                'body' => $m->body,
                'is_html' => $m->is_html,
                'created_at' => $m->created_at?->toIso8601String(),
                'user' => [
                    'id' => $m->user->id,
                    'name' => $m->user->name,
                    'profile_photo_url' => $m->user->profile_photo_url ?? null,
                    'profile_photo_path' => $m->user->profile_photo_path ?? null,
                ],
                'attachments' => $m->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'mime' => $a->mime,
                    'size' => $a->size,
                    'url'  => $a->url,
                ])->values(),
            ]
        ];
    }
}

