<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;

use App\Models\Chats\Attachment;
use App\Models\Chats\Conversation;
use App\Models\Chats\ConversationParticipant;
use App\Models\Chats\Message;
use App\Models\Chats\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Events\MessageSent;
use App\Events\ConversationUpdated;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    // GET /chats/{conversation}/messages?before_id=123
    public function index(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        abort_unless($conversation->participants()->where('user_id', $user->id)->exists(), 403);

        $limit = 10;
        $beforeId = $request->query('before_id');

        $q = $conversation->messages()->with('user')->orderByDesc('id');

        if ($beforeId) {
            $q->where('id', '<', $beforeId);
        }

        $messages = $q->take($limit)->get()->sortBy('id')->values();

        return response()->json([
            'data' => $messages,
            'next_page' => $messages->count() === $limit ? $messages->first()->id : null,
        ]);
    }

    // POST /chats/{conversation}/messages
    public function store(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        abort_unless($conversation->participants()->where('user_id', $user->id)->exists(), 403);

        $data = $request->validate([
            'body' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($conversation, $user, $data, $request) {
            // 1) Create message
            $msg = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'type' => $request->type,
                'body' => $data['body'] ?? null,
            ]);

            // 2) Mark sender as read + reset unread_count
            ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $user->id)
                ->update([
                    'last_read_message_id' => $msg->id,
                    'last_read_at' => now(),
                    'unread_count' => 0,
                ]);

            // 3) Participant IDs
            $participantIds = $conversation->participants()->pluck('user_id')->toArray();

            // 4) Broadcast MessageSent to conversation.{id} + users.{id}
            broadcast(new MessageSent($msg->load('user', 'attachments', 'reactions'), $participantIds))->toOthers();

            // 5) Broadcast ConversationUpdated for sidebar (each user gets own unread)
            foreach ($participantIds as $pid) {
                if ((int) $pid === (int) $user->id) {
                    $unread = 0;
                } else {
                    ConversationParticipant::where('conversation_id', $conversation->id)->where('user_id', $pid)->increment('unread_count');

                    $unread = ConversationParticipant::where('conversation_id', $conversation->id)->where('user_id', $pid)->value('unread_count') ?? 0;
                }

                broadcast(new ConversationUpdated($conversation->fresh(), $pid, $unread));
            }

            return response()->json($msg->load('user'));
        });
    }

    public function createConversation(Request $request)
    {
        $type = $request->input('type', 'dm');
        $authId = (int) Auth::id();

        // ───────────────────────────
        // DIRECT MESSAGE
        // ───────────────────────────
        if ($type === 'dm') {
            $data = $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);

            $peerId = (int) $data['user_id'];

            if ($peerId === $authId) {
                return response()->json(
                    [
                        'message' => 'You cannot start a direct message with yourself.',
                    ],
                    422,
                );
            }

            // Reuse existing DM where both are active participants
            $conv = Conversation::where('type', 'dm')
                ->whereHas('participants', function ($q) use ($authId) {
                    $q->where('user_id', $authId)->whereNull('left_at');
                })
                ->whereHas('participants', function ($q) use ($peerId) {
                    $q->where('user_id', $peerId)->whereNull('left_at');
                })
                ->with(['participants.user:id,name,profile_photo_path', 'lastMessage'])
                ->first();

            if (!$conv) {
                // Create a new DM
                $conv = DB::transaction(function () use ($authId, $peerId) {
                    $c = Conversation::create([
                        'type' => 'dm',
                        'name' => null,
                        'is_group' => false,
                        'created_by' => $authId,
                    ]);

                    $now = now();

                    ConversationParticipant::insert([
                        [
                            'conversation_id' => $c->id,
                            'user_id' => $authId,
                            'role' => 'member',
                            'joined_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'conversation_id' => $c->id,
                            'user_id' => $peerId,
                            'role' => 'member',
                            'joined_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    ]);

                    return $c->fresh(['participants.user:id,name,profile_photo_path', 'lastMessage']);
                });
            }

            // Build a nice display title + avatar from the "other" person
            $other = $conv->participants->firstWhere('user_id', '!=', $authId);
            $otherUser = optional($other)->user;

            $title = $otherUser?->name ?? 'Direct Message';
            $avatarUrl = $otherUser?->profile_photo_url ?? null;

            return response()->json([
                'id' => $conv->id,
                'is_group' => false,
                'title' => $title,
                'name' => $conv->name,
                'last_message' => optional($conv->lastMessage)->body,
                'last_at' => optional($conv->lastMessage)->created_at,
                'updated_at' => $conv->updated_at,
                'avatar_url' => $avatarUrl, // 👈 for DM sidebar + message bubbles
            ]);
        }

        // ───────────────────────────
        // GROUP CHAT
        // ───────────────────────────
        if ($type === 'group') {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'user_ids' => ['required', 'array', 'min:1'],
                'user_ids.*' => ['integer', 'exists:users,id'],
            ]);

            // Clean member IDs (no duplicates, remove self)
            $memberIds = array_unique(array_map('intval', $data['user_ids']));
            $memberIds = array_values(array_filter($memberIds, fn($id) => $id !== $authId));

            if (empty($memberIds)) {
                return response()->json(
                    [
                        'message' => 'Please select at least one other member for the group.',
                    ],
                    422,
                );
            }

            $conv = DB::transaction(function () use ($authId, $data, $memberIds) {
                $c = Conversation::create([
                    'type' => 'group',
                    'name' => $data['name'],
                    'is_group' => true,
                    'created_by' => $authId,
                ]);

                $now = now();
                $rows = [
                    [
                        'conversation_id' => $c->id,
                        'user_id' => $authId,
                        'role' => 'owner',
                        'joined_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ];

                foreach ($memberIds as $uid) {
                    $rows[] = [
                        'conversation_id' => $c->id,
                        'user_id' => $uid,
                        'role' => 'member',
                        'joined_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                ConversationParticipant::insert($rows);

                return $c->fresh(['participants.user:id,name,profile_photo_path', 'lastMessage']);
            });

            return response()->json([
                'id' => $conv->id,
                'is_group' => true,
                'title' => $conv->name ?: 'Group Chat',
                'name' => $conv->name,
                'last_message' => optional($conv->lastMessage)->body,
                'last_at' => optional($conv->lastMessage)->created_at,
                'updated_at' => $conv->updated_at,
                'avatar_url' => null, // 👈 groups still use the icon on frontend
            ]);
        }

        return response()->json(['message' => 'Invalid chat type'], 422);
    }

    // Fetch 10 messages (initial + older)
    // public function index(Request $request, Conversation $conversation)
    // {
    //     abort_unless(
    //         $conversation->participants()->where('user_id', $request->user()->id)->exists(),
    //         403
    //     );

    //     $limit    = 10;
    //     $beforeId = $request->query('before_id');

    //     $query = $conversation->messages()
    //         ->with('user')
    //         ->orderByDesc('id');

    //     if ($beforeId) {
    //         $query->where('id', '<', $beforeId);
    //     }

    //     $messages = $query->take($limit)->get()->sortBy('id')->values();

    //     return response()->json([
    //         'data'      => $messages,
    //         'next_page' => $messages->count() === $limit ? $messages->first()->id : null,
    //     ]);
    // }

    // // Store + broadcast
    // public function store(Request $request, Conversation $conversation)
    // {
    //     abort_unless(
    //         $conversation->participants()->where('user_id', $request->user()->id)->exists(),
    //         403
    //     );

    //     $request->validate([
    //         'body' => 'nullable|string',
    //     ]);

    //     $message = $conversation->messages()->create([
    //         'user_id' => Auth::id(),
    //         'body'    => $request->body,
    //     ]);

    //     // load for response
    //     $message->load('user');

    //     // participants
    //     $users = $conversation->participants()->pluck('user_id')->toArray();

    //     // Broadcast real-time
    //     broadcast(new MessageSent($message, $users))->toOthers();

    //     // (Optional) update side menu
    //     foreach ($users as $userId) {
    //         $unread = ConversationParticipant::where('conversation_id', $conversation->id)
    //             ->where('user_id', $userId)
    //             ->value('unread_count') ?? 0;

    //         broadcast(new ConversationUpdated($conversation->fresh(), $userId, $unread))->toOthers();
    //     }

    //     return response()->json($message);
    // }

    public function list(Request $request, Conversation $conversation)
    {
        // Ensure user is participant
        abort_unless(
            $conversation
                ->participants()
                ->where('user_id', $request->user()->id)
                ->exists(),
            403,
        );

        $messages = $conversation
            ->messages()
            ->with(['user', 'attachments', 'reactions'])
            ->latest('id')
            ->paginate(40);

        // Mirror conversation meet to each message for the poller
        $meet = (int) ($conversation->meet ?? 0);
        $messages->getCollection()->transform(function ($m) use ($meet) {
            $m->setAttribute('meet', $meet);
            return $m;
        });

        // (Optional) also expose it at the top-level if you like:
        // $messages->additional(['meet' => $meet]);

        return response()->json($messages);
    }

    public function markAsRead(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        abort_unless($conversation->participants()->where('user_id', $user->id)->exists(), 403);

        // Latest message in this conversation
        $latest = $conversation->messages()->latest()->first();

        // Update participant row
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update([
                'last_read_message_id' => $latest?->id,
                'last_read_at' => now(),
                'unread_count' => 0,
            ]);

        // Broadcast updated sidebar state with unread_count = 0 for this user
        broadcast(new ConversationUpdated($conversation->fresh(), $user->id, 0));

        return response()->json(['ok' => true]);
    }

    // public function store(Request $request, Conversation $conversation)
    // {
    //     abort_unless($conversation->participants()->where('user_id', $request->user()->id)->exists(), 403);

    //     $data = $request->validate([
    //         'type' => ['nullable', 'in:text,image,file,system,call'],
    //         'body' => ['nullable', 'string'],
    //         'reply_to_message_id' => ['nullable', 'integer', 'exists:t_chats_messages,id'],
    //         'attachments.*' => ['file', 'max:10240'],
    //         'meet' => ['nullable', 'in:0,1'],
    //     ]);

    //     return DB::transaction(function () use ($request, $conversation, $data) {
    //         $msg = Message::create([
    //             'conversation_id' => $conversation->id,
    //             'user_id' => $request->user()->id,
    //             'type' => $data['type'] ?? 'text',
    //             'body' => $data['body'] ?? null,
    //             'reply_to_message_id' => $data['reply_to_message_id'] ?? null,
    //         ]);

    //         if ($request->hasFile('attachments')) {
    //             foreach ($request->file('attachments') as $file) {
    //                 $path = '/storage/' . $file->store('chat', 'public');
    //                 Attachment::create([
    //                     'message_id' => $msg->id,
    //                     'user_id' => $request->user()->id,
    //                     'disk' => 'public',
    //                     'path' => $path,
    //                     'mime' => $file->getClientMimeType(),
    //                     'size' => $file->getSize(),
    //                 ]);
    //             }
    //         }
    //         // 3) OPTIONAL: if request asked to flip meeting → update conversation
    //         //    e.g. your “Send as System” button posts meet=1 with the system message
    //         if (isset($data['meet'])) {
    //             $conversation->meet = (int) $data['meet'];
    //             $conversation->save();
    //         }

    //         // Update sender read state
    //         ConversationParticipant::where('conversation_id', $conversation->id)
    //             ->where('user_id', $request->user()->id)
    //             ->update(['last_read_message_id' => $msg->id, 'last_read_at' => now()]);

    //         // broadcast(new MessageSent($msg->load('user','attachments')))->toOthers();
    //         $msg->load('user', 'attachments');
    //         $msg->setAttribute('meet', (int) ($conversation->meet ?? 0)); // <- key line

    //         return response()->json($msg);
    //     });
    // }

    public function update(Request $request, Message $message)
    {
        abort_unless($message->user_id === $request->user()->id, 403);
        $data = $request->validate(['body' => ['required', 'string']]);
        $message->update(['body' => $data['body'], 'edited_at' => now()]);
        return response()->json($message);
    }

    public function destroy(Request $request, Message $message)
    {
        abort_unless($message->user_id === $request->user()->id, 403);
        $message->delete(); // soft delete
        return response()->json(['ok' => true]);
    }

    public function react(Request $request, Message $message)
    {
        $data = $request->validate(['reaction' => ['required', 'string', 'max:32']]);
        $message->reactions()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'reaction' => $data['reaction'],
            ],
            [],
        );
        return response()->json(['ok' => true]);
    }

    public function unreact(Request $request, Message $message)
    {
        $data = $request->validate(['reaction' => ['required', 'string', 'max:32']]);
        $message
            ->reactions()
            ->where('user_id', $request->user()->id)
            ->where('reaction', $data['reaction'])
            ->delete();
        return response()->json(['ok' => true]);
    }

    public function pinMessage(Request $request, Message $message)
    {
        $conversation = $message->conversation;
        abort_unless(
            $conversation
                ->participants()
                ->where('user_id', $request->user()->id)
                ->exists(),
            403,
        );
        $conversation->pins()->firstOrCreate([
            'message_id' => $message->id,
            'user_id' => $request->user()->id,
        ]);
        return response()->json(['ok' => true]);
    }

    public function unpinMessage(Request $request, Message $message)
    {
        $conversation = $message->conversation;
        abort_unless(
            $conversation
                ->participants()
                ->where('user_id', $request->user()->id)
                ->exists(),
            403,
        );
        $conversation->pins()->where('message_id', $message->id)->delete();
        return response()->json(['ok' => true]);
    }
}
