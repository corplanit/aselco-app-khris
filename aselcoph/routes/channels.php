<?php

// use Illuminate\Support\Facades\Broadcast;
// use App\Models\Chats\Conversation;
// use App\Models\Chats\ConversationParticipant;

// Broadcast::routes([
//     'middleware' => ['auth'],
// ]);

// Broadcast::channel('users.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

// Broadcast::channel('conversations.{conversation}', function ($user, Conversation $conversation) {
//     return $conversation->participants()
//         ->where('user_id', $user->id)
//         ->exists();
// });
// Broadcast::channel('private-conversations.{conversation}', function ($user, Conversation $conversation) {
//     return $conversation->participants()
//         ->where('user_id', $user->id)
//         ->exists();
// });



use Illuminate\Support\Facades\Broadcast;
use App\Models\SuppConversation;

Broadcast::channel('supp.conversations.{conversationId}', function ($user, $conversationId) {
    $conv = SuppConversation::query()->find($conversationId);
    if (!$conv) return false;

    $isSupport = ($user->role === 'support' || $user->role === 'Administrator'
        || (method_exists($user, 'hasRole') && $user->hasRole('support')));

    // customer can only access their own conversation
    if ((int)$conv->customer_id === (int)$user->id) return true;

    // support can access any support conversation
    return $isSupport;
});

Broadcast::channel('supp.users.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
