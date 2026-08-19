<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuppConversation extends Model
{
    protected $table = 'supp_conversations';

    protected $fillable = [
        'customer_id','status','last_message_at','last_message_body',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SuppParticipant::class, 'conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SuppMessage::class, 'conversation_id');
    }
}
