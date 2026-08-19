<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuppMessage extends Model
{
    protected $table = 'supp_messages';

    protected $fillable = [
        'conversation_id','user_id','type','body','is_html',
    ];

    protected $casts = [
        'is_html' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SuppConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SuppAttachment::class, 'message_id');
    }
}
