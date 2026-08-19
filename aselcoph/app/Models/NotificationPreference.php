<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'billing',
        'service',
        'alert',
    ];

    protected $casts = [
        'billing' => 'boolean',
        'service' => 'boolean',
        'alert' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): self
    {
        return static::query()->firstOrCreate(
            ['user_id' => $userId],
            ['billing' => true, 'service' => true, 'alert' => true]
        );
    }

    public function allows(string $category): bool
    {
        return match ($category) {
            'billing' => (bool) $this->billing,
            'service' => (bool) $this->service,
            'alert' => (bool) $this->alert,
            default => true,
        };
    }
}
