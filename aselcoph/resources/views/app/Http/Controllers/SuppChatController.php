<?php

namespace App\Http\Controllers;

use App\Models\SuppConversation;
use App\Models\SuppParticipant;
use App\Models\User;
use Illuminate\Http\Request;

class SuppChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $isSupport = $this->isSupport($user);

        // Support: list all convos (open) with customer info + unread count for this support agent
        // Customer: ensure they have a conversation created and show only that thread
        if (!$isSupport) {
            $conv = $this->ensureCustomerConversation($user);
            $conversations = collect([$this->formatConversationForUi($conv, $user)]);
            return view('supp_chat.index', compact('conversations', 'isSupport'));
        }

        $convos = SuppConversation::query()
            ->with('customer')
            ->where('status', '!=', 'closed')
            ->orderByDesc('last_message_at')
            ->get();

        $conversations = $convos->map(fn($c) => $this->formatConversationForUi($c, $user));

        return view('supp_chat.index', compact('conversations', 'isSupport'));
    }

    public function ensureMine(Request $request)
    {
        $user = auth()->user();
        if ($this->isSupport($user)) {
            return response()->json(['ok' => true, 'conversation_id' => null]);
        }

        $conv = $this->ensureCustomerConversation($user);
        return response()->json(['ok' => true, 'conversation_id' => $conv->id]);
    }

    private function ensureCustomerConversation(User $customer): SuppConversation
    {
        $conv = SuppConversation::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            ['status' => 'open']
        );

        // Ensure customer is participant
        SuppParticipant::query()->firstOrCreate([
            'conversation_id' => $conv->id,
            'user_id' => $customer->id,
        ]);

        // Ensure all support members are participants
        $supportIds = User::query()
            ->whereIn('role', ['support', 'Administrator'])
            ->pluck('id')
            ->all();

        foreach ($supportIds as $sid) {
            SuppParticipant::query()->firstOrCreate([
                'conversation_id' => $conv->id,
                'user_id' => $sid,
            ]);
        }

        return $conv->load('customer');
    }

    private function formatConversationForUi(SuppConversation $c, User $auth): array
    {
        $isSupport = $this->isSupport($auth);

        // unread count: count messages > last_read_message_id for this user
        $p = SuppParticipant::query()
            ->where('conversation_id', $c->id)
            ->where('user_id', $auth->id)
            ->first();

        $lastReadId = $p?->last_read_message_id;

        $unread = $lastReadId
            ? $c->messages()->where('id', '>', $lastReadId)->where('user_id','!=',$auth->id)->count()
            : $c->messages()->where('user_id','!=',$auth->id)->count();

        $title = $isSupport ? ($c->customer?->name ?? 'Customer') : 'Support Team';

        return [
            'id' => $c->id,
            'title' => $title,
            'customer_id' => $c->customer_id,
            'customer_name' => $c->customer?->name ?? null,
            'customer_email' => $c->customer?->email ?? null,
            'last_message_body' => $c->last_message_body,
            'last_message_at' => optional($c->last_message_at ?? $c->updated_at)->toIso8601String(),
            'unread_count' => $unread,
            'avatar_url' => $c->customer?->profile_photo_url ?? null,
        ];
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
