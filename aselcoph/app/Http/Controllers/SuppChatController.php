<?php

namespace App\Http\Controllers;

use App\Models\SuppConversation;
use App\Models\SuppParticipant;
use App\Models\SuppMessage;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SuppChatController extends Controller
{
    // ✅ GET /supp/chat/poll/updates?after=ISO
    public function pollUpdates(Request $request)
    {
        $user = $request->user();

        $isSupport = ($user->role ?? null) === 'support' || ($user->role ?? null) === 'Administrator' || (method_exists($user, 'hasRole') && $user->hasRole('support'));

        $after = $request->query('after');
        $afterDt = null;

        if ($after) {
            try {
                $afterDt = Carbon::parse($after);
            } catch (\Throwable $e) {
                $afterDt = null;
            }
        }

        $q = SuppConversation::query()
            ->with('customer:id,name,profile_photo_path')
            ->when(!$isSupport, fn($qq) => $qq->where('customer_id', $user->id))
            ->when(
                $afterDt,
                fn($qq) => $qq->where(function ($w) use ($afterDt) {
                    $w->where('last_message_at', '>', $afterDt)->orWhere('updated_at', '>', $afterDt);
                }),
            )
            ->orderByDesc('last_message_at')
            ->limit(50);

        $rows = $q
            ->get()
            ->map(function ($conv) use ($user) {
                $lastReadId = DB::table('supp_participants')->where('conversation_id', $conv->id)->where('user_id', $user->id)->value('last_read_message_id');

                $lastReadId = (int) ($lastReadId ?: 0);

                $unread = DB::table('supp_messages')->where('conversation_id', $conv->id)->where('id', '>', $lastReadId)->where('user_id', '!=', $user->id)->count();

                $customer = $conv->customer;

                return [
                    'conversation_id' => $conv->id,
                    'title' => ($isSupport = true ? $customer?->name ?? 'Customer' : 'Support Team'),
                    'avatar_url' => $customer?->profile_photo_url ?? null,
                    'last_message' => (string) ($conv->last_message_body ?? ''),
                    'last_at' => optional($conv->last_message_at ?? $conv->updated_at)->toIso8601String(),
                    'unread_count' => (int) $unread,
                ];
            })
            ->values();

        return response()->json([
            'data' => $rows,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function index()
    {
        $user = auth()->user();
        $isSupport = $this->isSupport($user);

        if (!$isSupport) {
            $conv = $this->ensureCustomerConversation($user);
            $conversations = collect([$this->formatConversationForUi($conv, $user)]);
            return view('supp_chat.index', compact('conversations', 'isSupport'));
        }

        $convos = SuppConversation::query()->with('customer')->where('status', '!=', 'closed')->orderByDesc('last_message_at')->get();

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
        $conv = SuppConversation::query()->firstOrCreate(['customer_id' => $customer->id], ['status' => 'open']);

        SuppParticipant::query()->firstOrCreate([
            'conversation_id' => $conv->id,
            'user_id' => $customer->id,
        ]);

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

        $p = SuppParticipant::query()->where('conversation_id', $c->id)->where('user_id', $auth->id)->first();

        $lastReadId = (int) ($p?->last_read_message_id ?: 0);

        $unread = $c
            ->messages()
            ->when($lastReadId > 0, fn($q) => $q->where('id', '>', $lastReadId))
            ->where('user_id', '!=', $auth->id)
            ->count();

        $title = $isSupport ? $c->customer?->name ?? 'Customer' : 'Support Team';

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
        if (!$user) {
            return false;
        }

        if (($user->role ?? null) === 'support') {
            return true;
        }
        if (($user->role ?? null) === 'Administrator') {
            return true;
        }
        if (method_exists($user, 'hasRole') && $user->hasRole('support')) {
            return true;
        }

        return false;
    }

    // public function unreadTotal(Request $request)
    // {
    //     $user = $request->user();

    //     $isSupport = ($user->role ?? null) === 'support' || ($user->role ?? null) === 'Administrator' || (method_exists($user, 'hasRole') && $user->hasRole('support'));

    //     if (!$isSupport) {
    //         return response()->json(['total' => 0]);
    //     }

    //     // ✅ total unread = sum of messages after each participant's last_read_message_id excluding own messages
    //     $parts = SuppParticipant::query()
    //         ->where('user_id', $user->id)
    //         ->get(['conversation_id', 'last_read_message_id']);

    //     $total = 0;

    //     foreach ($parts as $p) {
    //         $q = SuppMessage::query()->where('conversation_id', $p->conversation_id)->where('user_id', '!=', $user->id);

    //         if ($p->last_read_message_id) {
    //             $q->where('id', '>', $p->last_read_message_id);
    //         }

    //         $total += $q->count();
    //     }

    //     return response()->json(['total' => (int) $total]);
    // }

    public function unreadTotal(Request $request)
    {
        $user = $request->user();
        $uid = (int) $user->id;

        $role = strtolower($user->role ?? '');
        $isSupport = in_array($role, ['support', 'administrator']) || (method_exists($user, 'hasRole') && $user->hasRole('support'));

        // ✅ SUPPORT/ADMIN: total unread across all conversations
        if ($isSupport) {
            $parts = SuppParticipant::query()
                ->where('user_id', $uid)
                ->get(['conversation_id', 'last_read_message_id']);

            $total = 0;

            foreach ($parts as $p) {
                $q = SuppMessage::query()->where('conversation_id', $p->conversation_id)->where('user_id', '!=', $uid);

                if ($p->last_read_message_id) {
                    $q->where('id', '>', $p->last_read_message_id);
                }

                $total += $q->count();
            }

            return response()->json(['total' => (int) $total]);
        }

        // ✅ CUSTOMER: unread only in their own conversation
        $conv = SuppConversation::query()->where('customer_id', $uid)->first();

        if (!$conv) {
            return response()->json(['total' => 0]);
        }

        $p = SuppParticipant::query()->where('conversation_id', $conv->id)->where('user_id', $uid)->first();

        $q = SuppMessage::query()->where('conversation_id', $conv->id)->where('user_id', '!=', $uid); // messages from support

        if ($p && $p->last_read_message_id) {
            $q->where('id', '>', $p->last_read_message_id);
        }

        return response()->json(['total' => (int) $q->count()]);
    }
}
