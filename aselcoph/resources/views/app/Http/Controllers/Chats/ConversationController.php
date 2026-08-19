<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use App\Models\Chats\Conversation;
use App\Models\Chats\ConversationParticipant;
use App\Models\Chats\Message;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\ConversationUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::forUser($user->id)
            ->with([
                'participants.user:id,name,profile_photo_path',
                'messages' => function ($q) {
                    $q->latest()->limit(1); // grab last message
                },
            ])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($conv) use ($user) {
                $isGroup = ($conv->type === 'group');

                if ($isGroup) {
                    $displayTitle = $conv->name ?? 'Group Chat';
                } else {
                    $other = $conv->participants
                        ->firstWhere('user_id', '!=', $user->id);

                    $displayTitle = optional(optional($other)->user)->name
                        ?? 'Direct Message';
                }

                $conv->display_title = $displayTitle;
                $conv->is_group      = $isGroup;

                $latest = $conv->messages->first();
                $conv->last_message_body = $latest?->body;
                $conv->last_message_at   = $latest?->created_at;

                $pivot = $conv->participants
                    ->where('user_id', $user->id)
                    ->first();

                $conv->unread_count = $pivot->unread_count ?? 0;

                return $conv;
            });

        return view('modules.chats.index', [
            'conversations' => $conversations,
        ]);
    }

    public function monitor(Request $request)
{
    $conversations = Conversation::with([
            'participants.user:id,name,profile_photo_path',
            'messages' => function ($q) {
                $q->latest()->limit(1); // grab last message
            },
        ])
        ->withCount('messages') // total messages per conversation
        ->orderByDesc('updated_at')
        ->get()
        ->map(function ($conv) {
            $isGroup = ($conv->type === 'group');

            // Display title: groups use name, DMs built from participants
            if ($isGroup) {
                $displayTitle = $conv->name ?: 'Group Chat #' . $conv->id;
            } else {
                // Build title from participant names (global monitor, no "current user")
                $names = $conv->participants
                    ->pluck('user.name')
                    ->filter()
                    ->unique()
                    ->values();

                if ($conv->name) {
                    $displayTitle = $conv->name;
                } elseif ($names->count() === 0) {
                    $displayTitle = 'Direct Message #' . $conv->id;
                } elseif ($names->count() === 1) {
                    $displayTitle = $names->first();
                } elseif ($names->count() === 2) {
                    $displayTitle = $names->join(' ↔ ');
                } else {
                    // more than 2 members (multi-dm or unnamed group)
                    $displayTitle = $names->take(3)->join(' • ');
                    if ($names->count() > 3) {
                        $displayTitle .= ' +' . ($names->count() - 3);
                    }
                }
            }

            $conv->display_title      = $displayTitle;
            $conv->is_group           = $isGroup;
            $conv->participants_count = $conv->participants->count();

            // Last message
            $latest = $conv->messages->first();
            $conv->last_message_body = $latest?->body;
            $conv->last_message_at   = $latest?->created_at;

            // Global unread: sum unread_count of all participants
            $conv->total_unread = $conv->participants->sum('unread_count');

            return $conv;
        });

    return view('modules.chats.monitor', [
        'conversations' => $conversations,
    ]);
}

public function show($id)
{
    $conversation = Conversation::with([
        'participants.user:id,name,email,profile_photo_path',
        'messages.user:id,name,email,profile_photo_path',
    ])->findOrFail($id);

    $isGroup = $conversation->type === 'group';

    // Build display_title similar to monitor
    if ($isGroup) {
        $displayTitle = $conversation->name ?: 'Group Chat #' . $conversation->id;
    } else {
        $names = $conversation->participants
            ->pluck('user.name')
            ->filter()
            ->unique()
            ->values();

        if ($conversation->name) {
            $displayTitle = $conversation->name;
        } elseif ($names->count() === 0) {
            $displayTitle = 'Direct Message #' . $conversation->id;
        } elseif ($names->count() === 1) {
            $displayTitle = $names->first();
        } elseif ($names->count() === 2) {
            $displayTitle = $names->join(' ↔ ');
        } else {
            $displayTitle = $names->take(3)->join(' • ');
            if ($names->count() > 3) {
                $displayTitle .= ' +' . ($names->count() - 3);
            }
        }
    }

    $conversation->display_title = $displayTitle;
    $conversation->is_group      = $isGroup;

    return view('modules.chats.show', compact('conversation'));
}


    /**
     * Open or create a DM between auth user and peer.
     */
    public function open(Request $request)
    {
        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);

        $me   = (int) $request->user()->id;
        $peer = (int) $request->input('user_id');

        if ($peer === $me) {
            return response()->json(['message' => 'Cannot DM yourself.'], 422);
        }

        $existing = Conversation::query()
            ->where('type', 'dm')
            ->whereHas('participants', fn($q) => $q->whereIn('user_id', [$me, $peer])->whereNull('left_at'))
            ->with([
                'participants' => fn($q) => $q->whereNull('left_at')
                    ->with('user:id,name,profile_photo_path'),
                'messages' => fn($q) => $q->latest()->limit(1)
            ])
            ->get()
            ->first(function ($c) use ($me, $peer) {
                if ($c->participants->count() !== 2) return false;
                $ids = $c->participants->pluck('user_id')->map(fn($v) => (int)$v)->sort()->values()->all();
                return $ids === [min($me, $peer), max($me, $peer)];
            });

        if ($existing) {
            return response()->json(['conversation' => $existing]);
        }

        $conv = DB::transaction(function () use ($me, $peer) {
            $c = Conversation::create([
                'type'       => 'dm',
                'name'       => null,
                'created_by' => $me,
            ]);

            ConversationParticipant::insert([
                [
                    'conversation_id' => $c->id,
                    'user_id'         => $me,
                    'role'            => 'member',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'conversation_id' => $c->id,
                    'user_id'         => $peer,
                    'role'            => 'member',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
            ]);

            return $c;
        });

        $conv->load([
            'participants' => fn($q) => $q->with('user:id,name,profile_photo_path')->whereNull('left_at'),
            'messages'     => fn($q) => $q->latest()->limit(1)
        ]);

        return response()->json(['conversation' => $conv], 201);
    }

    /**
     * Legacy DM creation (still available if used somewhere else).
     */
    public function storeDm(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id', 'different:auth'],
        ]);
        $authId = $request->user()->id;
        $peerId = (int) $data['user_id'];

        return DB::transaction(function () use ($authId, $peerId) {
            $conv = Conversation::where('type', 'dm')
                ->whereHas('participants', fn($q) => $q->where('user_id', $authId))
                ->whereHas('participants', fn($q) => $q->where('user_id', $peerId))
                ->first();

            if (!$conv) {
                $conv = Conversation::create([
                    'type'       => 'dm',
                    'created_by' => $authId,
                ]);

                foreach ([$authId, $peerId] as $uid) {
                    ConversationParticipant::create([
                        'conversation_id' => $conv->id,
                        'user_id'         => $uid,
                        'role'            => $uid === $authId ? 'owner' : 'member',
                        'joined_at'       => now(),
                    ]);
                }
            }

            return response()->json($conv->load('participants.user'));
        });
    }

    /**
     * Legacy group creation (still available if used).
     */
    public function storeGroup(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'member_ids'  => ['array', 'min:1'],
            'member_ids.*'=> ['integer', 'exists:users,id'],
            'photo'       => ['nullable', 'string'],
        ]);
        $authId = $request->user()->id;

        return DB::transaction(function () use ($authId, $data) {
            $conv = Conversation::create([
                'type'       => 'group',
                'name'       => $data['name'],
                'photo'      => $data['photo'] ?? null,
                'created_by' => $authId,
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conv->id,
                'user_id'         => $authId,
                'role'            => 'owner',
                'joined_at'       => now(),
            ]);

            foreach (($data['member_ids'] ?? []) as $uid) {
                if ($uid === $authId) continue;
                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id'         => $uid,
                    'role'            => 'member',
                    'joined_at'       => now(),
                ]);
            }

            return response()->json($conv->load('participants.user'));
        });
    }

    public function listMembers(Request $request, Conversation $conversation)
    {
        $participants = ConversationParticipant::with(['user:id,name,profile_photo_path'])
            ->where('conversation_id', $conversation->id)
            ->whereNull('left_at')
            ->get()
            ->map(function ($p) {
                $u = $p->user;
                return [
                    'id'        => $u->id,
                    'name'      => $u->name,
                    'avatar'    => $u->profile_photo_path
                        ? asset('storage/' . $u->profile_photo_path)
                        : url('/user.png'),
                    'role'      => $p->role ?? 'Member',
                    'joined_at' => optional($p->joined_at)->toIso8601String(),
                ];
            });

        $conversation->loadCount([
            'messages',
            'attachments as files_count',
            'imageAttachments as images_count',
            'fileAttachments as docs_count',
        ]);

        return response()->json([
            'participants' => $participants,
            'conversation' => [
                'id'             => $conversation->id,
                'name'           => $conversation->name,
                'photo'          => $conversation->photo ? asset('storage/' . $conversation->photo) : url('/user.png'),
                'messages_count' => $conversation->messages_count,
                'files_count'    => $conversation->files_count,
                'last_activity'  => optional($conversation->updated_at)->diffForHumans(),
            ],
        ]);
    }

    public function add(Request $request, Conversation $conversation)
    {
        if ($request->filled('member_ids')) {
            $data = $request->validate([
                'member_ids'   => ['required', 'array', 'min:1'],
                'member_ids.*' => ['integer', 'exists:users,id'],
            ]);

            $userIds = $data['member_ids'];
        } else {
            $data = $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
            ]);

            $user = User::where('email', $data['email'])->firstOrFail();
            $userIds = [$user->id];
        }

        foreach ($userIds as $uid) {
            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $uid],
                ['joined_at' => now(), 'left_at' => null]
            );
        }

        return response()->json(['ok' => true]);
    }

    public function remove(Request $request, Conversation $conversation, User $user)
    {
        $pivot = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$pivot) {
            return response()->json(['ok' => true, 'message' => 'Already removed'], 200);
        }

        $pivot->left_at = now();
        $pivot->save();

        return response()->json(['ok' => true]);
    }

    public function attachments(Conversation $conversation)
    {
        $list = $conversation->attachments()
            ->select('chat_attachments.*')
            ->latest('chat_attachments.id')
            ->limit(50)
            ->get()
            ->map(function ($a) {
                $url = $a->disk
                    ? \Storage::disk($a->disk)->url($a->path)
                    : asset($a->path);

                $thumb = (is_string($a->mime) && str_starts_with($a->mime, 'image/')) ? $url : null;

                return [
                    'id'         => $a->id,
                    'url'        => $url,
                    'thumb'      => $thumb,
                    'name'       => basename($a->path),
                    'mime'       => $a->mime,
                    'size'       => (int) ($a->size ?? 0),
                    'created_at' => optional($a->created_at)->toIso8601String(),
                ];
            });

        return response()->json($list);
    }

    public function rename(Request $request, Conversation $conversation)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $conversation->name = $data['name'];
        $conversation->save();

        return response()->json([
            'ok'           => true,
            'conversation' => [
                'id'    => $conversation->id,
                'name'  => $conversation->name,
                'photo' => $conversation->photo ? asset('storage/' . $conversation->photo) : url('/user.png'),
            ],
        ]);
    }

    public function search(Request $request)
    {
        $q = $request->query('q', '');

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $users = User::query()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'profile_photo_path']);

        $results = $users->map(function ($u) {
            return [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
                'avatar' => $u->profile_photo_path
                    ? asset('storage/' . $u->profile_photo_path)
                    : url('/user.png'),
            ];
        });

        return response()->json($results);
    }

    public function addMembers(Request $request, Conversation $conversation)
    {
        $data = $request->validate([
            'member_ids'   => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        foreach ($data['member_ids'] as $uid) {
            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $uid],
                ['joined_at' => now(), 'left_at' => null]
            );
        }

        return response()->json(['ok' => true]);
    }

    public function leave(Request $request, Conversation $conversation)
    {
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $request->user()->id)
            ->update(['left_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // Per-user flags
    public function pin(Request $request, Conversation $conversation)
    {
        return $this->setFlag($request, $conversation, 'is_pinned', true);
    }

    public function unpin(Request $request, Conversation $conversation)
    {
        return $this->setFlag($request, $conversation, 'is_pinned', false);
    }

    public function archive(Request $request, Conversation $conversation)
    {
        return $this->setFlag($request, $conversation, 'is_archived', true);
    }

    public function unarchive(Request $request, Conversation $conversation)
    {
        return $this->setFlag($request, $conversation, 'is_archived', false);
    }

    public function trash(Request $request, Conversation $conversation)
    {
        return $this->setFlag($request, $conversation, 'is_trashed', true);
    }

    public function restore(Request $request, Conversation $conversation)
    {
        return $this->setFlag($request, $conversation, 'is_trashed', false);
    }

    protected function setFlag(Request $request, Conversation $conversation, string $flag, bool $value)
    {
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $request->user()->id)
            ->update([$flag => $value]);

        return response()->json(['ok' => true, $flag => $value]);
    }

    public function markRead(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        $lastMessage = $conversation->messages()->latest('id')->first();
        if ($lastMessage) {
            ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $user->id)
                ->update([
                    'last_read_message_id' => $lastMessage->id,
                    'last_read_at'         => now(),
                ]);
        }
        return response()->json(['ok' => true]);
    }

    public function info(Request $request, Conversation $conversation)
    {
        return response()->json(['conv' => $conversation]);
    }

    public function joined(Request $request, Conversation $conversation)
    {
        $conversation->increment('joined');
        return response()->json(['conv' => $conversation]);
    }

    public function left(Request $request, Conversation $conversation)
    {
        if ($conversation->joined > 0) {
            $conversation->decrement('joined');
            Message::create([
                'conversation_id' => $conversation->id,
                'user_id'         => Auth::user()->id,
                'type'            => 'system',
                'body'            => Auth::user()->name . ' Left the Meeting.',
            ]);
        }

        return response()->json(['conv' => $conversation]);
    }

    public function end(Request $request, Conversation $conversation)
    {
        $conversation->meet   = null;
        $conversation->joined = 0;
        $conversation->save();

        return response()->json(['convx' => $conversation]);
    }

    /* ============================================================
     * NEW: Used by "New Chat" modal + info modal
     * ============================================================
     */

    /**
     * Create a new conversation from modal:
     * - type=dm    -> DM with user_id
     * - type=group -> group with name + optional member_ids[]
     */
    public function store(Request $request)
{
    $authId = $request->user()->id;

    // Validate dynamically based on type
    $data = $request->validate([
        'type' => ['required', 'in:dm,group'],

        // For DM: user_id is required
        'user_id' => [
            'required_if:type,dm',
            'nullable',
            'integer',
            'exists:users,id',
        ],

        // For Group: name is required
        'name' => [
            'required_if:type,group',
            'nullable',
            'string',
            'max:255',
        ],

        // For Group: list of member_ids (optional)
        'member_ids'   => ['nullable', 'array'],
        'member_ids.*' => ['integer', 'exists:users,id'],
    ]);

    if ($data['type'] === 'dm') {
        // At this point, user_id is guaranteed to be present & valid
        $otherId = (int) $data['user_id'];

        return $this->createDirectMessage($authId, $otherId);
    }

    // type = group
    $memberIds = $data['member_ids'] ?? [];

    return $this->createGroupChat($authId, $data['name'], $memberIds);
}

    protected function createDirectMessage(int $authId, int $otherId)
    {
        if ($otherId === $authId) {
            return response()->json([
                'message' => 'You cannot create a DM with yourself.',
            ], 422);
        }

        $existing = Conversation::query()
            ->where('type', 'dm')
            ->whereHas('participants', fn($q) => $q->where('user_id', $authId)->whereNull('left_at'))
            ->whereHas('participants', fn($q) => $q->where('user_id', $otherId)->whereNull('left_at'))
            ->with([
                'participants.user:id,name,profile_photo_path',
                'messages' => fn($q) => $q->latest()->limit(1),
            ])
            ->first();

        if ($existing) {
            return response()->json($this->makeSidebarPayload($existing));
        }

        $conversation = DB::transaction(function () use ($authId, $otherId) {
            $conv = Conversation::create([
                'type'       => 'dm',
                'name'       => null,
                'created_by' => $authId,
            ]);

            $now = now();
            ConversationParticipant::insert([
                [
                    'conversation_id' => $conv->id,
                    'user_id'         => $authId,
                    'role'            => 'member',
                    'joined_at'       => $now,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ],
                [
                    'conversation_id' => $conv->id,
                    'user_id'         => $otherId,
                    'role'            => 'member',
                    'joined_at'       => $now,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ],
            ]);

            return $conv;
        });

        $conversation->load([
            'participants.user:id,name,profile_photo_path',
            'messages' => fn($q) => $q->latest()->limit(1),
        ]);

        $this->broadcastConversationUpdated($conversation);

        return response()->json($this->makeSidebarPayload($conversation));
    }

    protected function createGroupChat(int $authId, string $name, array $memberIds)
    {
        $memberIds = collect($memberIds)
            ->map(fn($id) => (int) $id)
            ->filter()
            ->push($authId)
            ->unique()
            ->values()
            ->all();

        if (count($memberIds) < 2) {
            return response()->json([
                'message' => 'Please select at least one other member.',
            ], 422);
        }

        $conversation = DB::transaction(function () use ($authId, $name, $memberIds) {
            $conv = Conversation::create([
                'type'       => 'group',
                'name'       => $name,
                'created_by' => $authId,
            ]);

            $now = now();
            $rows = [];
            foreach ($memberIds as $uid) {
                $rows[] = [
                    'conversation_id' => $conv->id,
                    'user_id'         => $uid,
                    'role'            => $uid === $authId ? 'owner' : 'member',
                    'joined_at'       => $now,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            ConversationParticipant::insert($rows);

            return $conv;
        });

        $conversation->load([
            'participants.user:id,name,profile_photo_path',
            'messages' => fn($q) => $q->latest()->limit(1),
        ]);

        $this->broadcastConversationUpdated($conversation);

        return response()->json($this->makeSidebarPayload($conversation));
    }

    protected function makeSidebarPayload(Conversation $conversation): array
    {
        $conversation->loadMissing([
            'participants.user:id,name,profile_photo_path',
            'messages' => fn($q) => $q->latest()->limit(1),
        ]);

        $isGroup = $conversation->type === 'group';

        if ($isGroup) {
            $displayTitle = $conversation->name ?? 'Group Chat';
        } else {
            $authId = Auth::id();
            $other  = $conversation->participants
                ->firstWhere('user_id', '!=', $authId);

            $displayTitle = optional(optional($other)->user)->name ?? 'Direct Message';
        }

        $latest = $conversation->messages->first();
        $lastAt = $latest?->created_at ?? $conversation->updated_at;

        return [
            'id'             => $conversation->id,
            'conversation_id'=> $conversation->id,
            'is_group'       => $isGroup,
            'title'          => $displayTitle,
            'last_message'   => $latest?->body_html ?? $latest?->body ?? '',
            'last_at'        => $lastAt ? $lastAt->toIso8601String() : null,
            'updated_at'     => $conversation->updated_at
                                ? $conversation->updated_at->toIso8601String()
                                : null,
            'unread_count'   => 0,
            'avatar_url'     => null,
        ];
    }

    protected function broadcastConversationUpdated(Conversation $conversation): void
    {
        $conversation->loadMissing([
            'participants.user',
            'messages' => fn ($q) => $q->latest()->limit(1),
        ]);

        foreach ($conversation->participants as $participant) {
            $userId      = $participant->user_id;
            $unreadCount = $participant->unread_count ?? 0;

            ConversationUpdated::dispatch(
                $conversation,
                $userId,
                $unreadCount
            );
        }
    }

    /**
     * Rename group chat (info modal).
     */
    public function update(Request $request, Conversation $conversation)
    {
        $this->authorizeManage($conversation);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $conversation->name = $data['name'];
        $conversation->save();

        $this->broadcastConversationUpdated($conversation);

        return response()->json(['ok' => true]);
    }

    /**
     * Delete group chat (info modal "Delete").
     */
    public function destroy(Conversation $conversation)
    {
        $this->authorizeManage($conversation);

        DB::transaction(function () use ($conversation) {
            if (method_exists($conversation, 'messages')) {
                $conversation->messages()->delete();
            }

            ConversationParticipant::where('conversation_id', $conversation->id)->delete();
            $conversation->delete();
        });

        return response()->json(['ok' => true]);
    }

    /**
     * Add member by email (info modal).
     */
    public function addMember(Request $request, Conversation $conversation)
    {
        $this->authorizeManage($conversation);

        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'User with that email was not found.',
            ], 404);
        }

        ConversationParticipant::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['joined_at' => now(), 'left_at' => null]
        );

        $this->broadcastConversationUpdated($conversation);

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Remove member (info modal).
     */
    public function removeMember(Conversation $conversation, User $user)
    {
        $this->authorizeManage($conversation);

        if ((int) $user->id === (int) $conversation->created_by) {
            return response()->json([
                'message' => 'You cannot remove the group owner.',
            ], 422);
        }

        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->delete();

        $this->broadcastConversationUpdated($conversation);

        return response()->json(['ok' => true]);
    }

    /**
     * Mark conversation as read for current user (used by JS /chats/{id}/read).
     */
    public function markAsRead(Conversation $conversation)
    {
        $userId = Auth::id();

        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->update([
                'last_read_at' => now(),
                'unread_count' => 0,
            ]);

        ConversationUpdated::dispatch($conversation->fresh(), $userId, 0);

        return response()->json(['ok' => true]);
    }

    /* ----------------- helpers ----------------- */

    protected function authorizeManage(Conversation $conversation): void
    {
        $userId = Auth::id();

        if ($conversation->type !== 'group' || (int) $conversation->created_by !== (int) $userId) {
            abort(403, 'You are not allowed to manage this group.');
        }
    }
}
