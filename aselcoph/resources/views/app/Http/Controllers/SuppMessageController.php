<?php

namespace App\Http\Controllers;

use App\Events\SuppConversationUpdated;
use App\Events\SuppMessageSent;
use App\Models\SuppAttachment;
use App\Models\SuppConversation;
use App\Models\SuppMessage;
use App\Models\SuppParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SuppMessageController extends Controller
{
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

        // broadcast message to current conversation listeners
        $msg->load(['user','attachments']);
        broadcast(new SuppMessageSent($msg))->toOthers();

        // notify participants (only compute what they need)
        $participantIds = SuppParticipant::query()
            ->where('conversation_id', $conv->id)
            ->pluck('user_id')
            ->map(fn($v) => (int)$v)
            ->unique()
            ->values()
            ->all();

        // (safety) ensure customer is included
        if (!in_array((int)$conv->customer_id, $participantIds, true)) {
            $participantIds[] = (int)$conv->customer_id;
        }

        foreach ($participantIds as $uid) {
            // unread for that user in this conversation
            $unreadForConv = $this->unreadForConversationUser($conv, $uid);

            // total unread for that user (for global badge)
            $totalUnread = $this->totalUnreadForUser($uid);

            $recipient = User::find($uid);
            $isSupportRecipient = $this->isSupport($recipient);
            $title = $isSupportRecipient ? ($conv->customer?->name ?? 'Customer') : 'Support Team';

            broadcast(new SuppConversationUpdated($uid, [
                'conversation_id' => $conv->id,
                'title' => $title,
                'last_message' => $conv->last_message_body ?? '',
                'last_at' => now()->toIso8601String(),
                'unread_count' => $unreadForConv,
                'total_unread' => $totalUnread,
                'avatar_url' => $conv->customer?->profile_photo_url ?? null,
            ]))->toOthers();
        }

        return response()->json($this->shapeMessage($msg));
    }

    public function read(Request $request, $conversationId)
    {
        $user = auth()->user();
        $conv = SuppConversation::query()->with('customer')->findOrFail($conversationId);

        $this->authorizeConversation($user, $conv);

        $lastMsgId = (int)(SuppMessage::query()
            ->where('conversation_id', $conv->id)
            ->max('id') ?? 0);

        // ✅ Mode A (recommended): per-user read
        if (!config('supp.shared_support_inbox', false)) {
            SuppParticipant::query()->updateOrCreate(
                ['conversation_id' => $conv->id, 'user_id' => (int)$user->id],
                ['last_read_message_id' => $lastMsgId, 'last_read_at' => now()]
            );

            // If you still want to update THIS user's global badge in realtime:
            $totalUnread = $this->totalUnreadForUser((int)$user->id);

            broadcast(new SuppConversationUpdated((int)$user->id, [
                'conversation_id' => $conv->id,
                'title' => $this->isSupport($user) ? ($conv->customer?->name ?? 'Customer') : 'Support Team',
                'last_message' => $conv->last_message_body ?? '',
                'last_at' => $conv->last_message_at?->toIso8601String() ?? now()->toIso8601String(),
                'unread_count' => 0,
                'total_unread' => $totalUnread,
                'avatar_url' => $conv->customer?->profile_photo_url ?? null,
            ]))->toOthers();

            return response()->json(['ok' => true]);
        }

        // ✅ Mode B (shared inbox): ONE upsert for all supports
        if ($this->isSupport($user)) {
            $supportIds = $this->supportIds(); // cached list

            $rows = [];
            $now = now();
            foreach ($supportIds as $sid) {
                $rows[] = [
                    'conversation_id' => (int)$conv->id,
                    'user_id' => (int)$sid,
                    'last_read_message_id' => $lastMsgId,
                    'last_read_at' => $now,
                ];
            }

            // Single query (not loop of updateOrCreate)
            // Requires unique index (conversation_id, user_id) recommended
            DB::table('supp_participants')->upsert(
                $rows,
                ['conversation_id', 'user_id'],
                ['last_read_message_id', 'last_read_at']
            );

            // Optional realtime notify each support
            foreach ($supportIds as $sid) {
                $totalUnread = $this->totalUnreadForUser((int)$sid);

                broadcast(new SuppConversationUpdated((int)$sid, [
                    'conversation_id' => $conv->id,
                    'title' => $conv->customer?->name ?? 'Customer',
                    'last_message' => $conv->last_message_body ?? '',
                    'last_at' => $conv->last_message_at?->toIso8601String() ?? now()->toIso8601String(),
                    'unread_count' => 0,
                    'total_unread' => $totalUnread,
                    'avatar_url' => $conv->customer?->profile_photo_url ?? null,
                ]))->toOthers();
            }

            return response()->json(['ok' => true]);
        }

        // customer read in shared mode (still per-customer)
        SuppParticipant::query()->updateOrCreate(
            ['conversation_id' => $conv->id, 'user_id' => (int)$user->id],
            ['last_read_message_id' => $lastMsgId, 'last_read_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    // GET /supp/unread-total
    public function unreadTotal()
    {
        $uid = (int)auth()->id();
        return response()->json([
            'total_unread' => $this->totalUnreadForUser($uid),
        ]);
    }

    // ===========================
    // Helpers
    // ===========================

    private function unreadForConversationUser(SuppConversation $conv, int $uid): int
    {
        $p = SuppParticipant::query()
            ->where('conversation_id', (int)$conv->id)
            ->where('user_id', (int)$uid)
            ->first();

        $lastReadId = (int)($p?->last_read_message_id ?? 0);

        $q = SuppMessage::query()->where('conversation_id', (int)$conv->id);

        $u = User::find($uid);

        // ✅ Support counts only CUSTOMER messages
        if ($this->isSupport($u)) {
            $q->where('user_id', (int)$conv->customer_id);
        } else {
            // ✅ Customer counts only SUPPORT messages
            $q->where('user_id', '!=', (int)$conv->customer_id);
        }

        if ($lastReadId > 0) {
            $q->where('id', '>', $lastReadId);
        }

        return (int)$q->count();
    }

    private function totalUnreadForUser(int $uid): int
    {
        // IMPORTANT: this loops conversations the user is participant of
        // (If supports are not in supp_participants for every conversation,
        // you should ensure they are added when creating/ensuring conversations.)
        $conversationIds = SuppParticipant::query()
            ->where('user_id', $uid)
            ->pluck('conversation_id')
            ->map(fn($v) => (int)$v)
            ->unique()
            ->values()
            ->all();

        $total = 0;
        foreach ($conversationIds as $cid) {
            $conv = SuppConversation::query()->find($cid);
            if (!$conv) continue;
            $total += $this->unreadForConversationUser($conv, $uid);
        }

        return (int)$total;
    }

    private function shapeMessage(SuppMessage $m): array
    {
        return [
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
            'attachments' => $m->attachments->map(function ($a) {
                $url = null;
                try {
                    $url = Storage::disk($a->disk ?? 'public')->url($a->path);
                } catch (\Throwable $e) {
                    $url = null;
                }

                return [
                    'id' => $a->id,
                    'name' => $a->name,
                    'mime' => $a->mime,
                    'size' => $a->size,
                    'url'  => $url,
                ];
            })->values(),
        ];
    }

    private function authorizeConversation($user, SuppConversation $conv): void
    {
        if ((int)$conv->customer_id === (int)$user->id) return;
        if ($this->isSupport($user)) return;

        abort(403, 'Not allowed.');
    }

    private function isSupport(?User $user): bool
    {
        if (!$user) return false;

        $role = strtolower((string)($user->role ?? ''));

        if ($role === 'support') return true;
        if ($role === 'administrator') return true;

        if (method_exists($user, 'hasRole') && $user->hasRole('support')) return true;

        return false;
    }

    private function supportIds(): array
    {
        static $cached = null;
        if (is_array($cached)) return $cached;

        $cached = User::query()
            ->whereIn('role', ['support', 'administrator', 'Administrator'])
            ->pluck('id')
            ->map(fn($v) => (int)$v)
            ->unique()
            ->values()
            ->all();

        return $cached;
    }
}
