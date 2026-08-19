<?php

namespace App\Models\Chats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class ConversationParticipant extends Model
{
    protected $table = 'chat_conversation_participants';

    protected $fillable = [
        'conversation_id','user_id','role','is_pinned','is_archived','is_trashed','is_muted','last_read_message_id','last_read_at','joined_at','left_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_archived' => 'boolean',
        'is_trashed' => 'boolean',
        'is_muted' => 'boolean',
        'last_read_at' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function conversation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(Conversation::class); }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(User::class); }
}
