<?php

namespace App\Models\Chats;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MessagePin extends Model
{
    protected $table = 'chat_message_pins';

    protected $fillable = ['conversation_id','message_id','user_id'];

    public function conversation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(Conversation::class); }
    public function message(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(Message::class); }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(User::class); }
}
