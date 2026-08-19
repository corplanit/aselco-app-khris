<?php

namespace App\Models\Chats;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Attachment extends Model
{

    protected $table = 'chat_attachments';
    protected $fillable = [
        'message_id','user_id','disk','path','mime','size','width','height','meta'
    ];

    protected $casts = [ 'meta' => 'array' ];
    
    public function message(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(Message::class); }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(User::class); }
}
