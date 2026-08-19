<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    public const AUDIENCE_ALL = 'all';

    public const AUDIENCE_USERS = 'users';

    public const AUDIENCE_METER = 'meter';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'body',
        'category',
        'audience_type',
        'audience_user_ids',
        'meter_numbers',
        'status',
        'published_at',
        'sent_count',
        'created_by',
    ];

    protected $casts = [
        'audience_user_ids' => 'array',
        'meter_numbers' => 'array',
        'published_at' => 'datetime',
        'sent_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
