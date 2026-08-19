<?php

namespace App\Http\Controllers;

use App\Models\SuppAttachment;
use App\Models\SuppConversation;
use App\Models\SuppMessage;
use App\Models\SuppParticipant;
use App\Models\User;
use Illuminate\Http\Request;

class SuppMessageController extends Controller
{
    // ✅ GET /supp/chat/{conversationId}/messages/new?after_id=123
    public function newMessages(Request $request, $conversationId)
    {
        $user = $request->user();
        $afterId = (int) $request->query('after_id', 0);

        $conv = SuppConversation::with('customer')->findOrFail($conversationId);

        $this->authorizeConversation($user, $conv);

        $items = SuppMessage::query()
            ->with(['user', 'attachments'])
            ->where('conversation_id', $conv->id)
            ->where('id', '>', $afterId)
            ->orderBy('id', 'asc')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $items->map(fn($m) => $this->shapeMessage($m)),
            'last_id' => $items->max('id') ?: $afterId,
        ]);
    }

    public function messages(Request $request, $conversationId)
    {
        $user = auth()->user();
        $conv = SuppConversation::query()->with('customer')->findOrFail($conversationId);

        $this->authorizeConversation($user, $conv);

        $beforeId = $request->query('before_id');

        $q = SuppMessage::query()
            ->with(['user','attachments'])
            ->where('conversation_id', $conv->id)
            ->orderByDesc('id');

        if ($beforeId) {
            $q->where('id', '<', (int)$beforeId);
        }

        $items = $q->limit(20)->get()->reverse()->values();
        $nextPage = $items->count() ? $items->first()->id : null;

        return response()->json([
            'data' => $items->map(fn($m) => $this->shapeMessage($m)),
            'next_page' => $nextPage ? (string)$nextPage : null,
        ]);
    }

    public function send(Request $request, $conversationId)
    {
        $user = auth()->user();
        $conv = SuppConversation::query()->with('customer')->findOrFail($conversationId);

        $this->authorizeConversation($user, $conv);

        $request->validate([
            'type' => 'nullable|string',
            'body' => 'nullable|string',
            'is_html' => 'nullable',
            'attachments.*' => 'file|max:8192',
        ]);

        $msg = SuppMessage::query()->create([
            'conversation_id' => $conv->id,
            'user_id' => $user->id,
            'type' => $request->input('type', 'text'),
            'body' => $request->input('body', ''),
            'is_html' => (bool)$request->input('is_html', true),
        ]);

        // attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('supp_chat', 'public');

                SuppAttachment::query()->create([
                    'message_id' => $msg->id,
                    'disk' => 'public',
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // update conversation last message
        $conv->update([
            'last_message_at' => now(),
            'last_message_body' => $msg->body,
        ]);

        $msg->load(['user','attachments']);

        return response()->json($this->shapeMessage($msg));
    }

    public function read(Request $request, $conversationId)
    {
        $user = auth()->user();
        $conv = SuppConversation::query()->findOrFail($conversationId);

        $this->authorizeConversation($user, $conv);

        $lastMsgId = (int) SuppMessage::query()
            ->where('conversation_id', $conv->id)
            ->max('id');

        SuppParticipant::query()->updateOrCreate(
            ['conversation_id' => $conv->id, 'user_id' => $user->id],
            ['last_read_message_id' => $lastMsgId, 'last_read_at' => now()]
        );

        return response()->json(['ok' => true, 'last_read_message_id' => $lastMsgId]);
    }

    private function shapeMessage(SuppMessage $m): array
    {
        return [
            'id' => $m->id,
            'conversation_id' => $m->conversation_id,
            'type' => $m->type,
            'body' => $m->body,
            'is_html' => (bool)$m->is_html,
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
                'url'  => $a->url ?? asset('storage/'.$a->path),
            ])->values(),
        ];
    }

    private function authorizeConversation($user, SuppConversation $conv): void
    {
        $isSupport = $this->isSupport($user);

        if ((int)$conv->customer_id === (int)$user->id) return;
        if ($isSupport) return;

        abort(403, 'Not allowed.');
    }

    private function isSupport(?User $user): bool
    {
        if (!$user) return false;
        if (($user->role ?? null) === 'support') return true;
        if (($user->role ?? null) === 'Administrator') return true;
        if (method_exists($user, 'hasRole') && $user->hasRole('support')) return true;
        return false;
    }
}
