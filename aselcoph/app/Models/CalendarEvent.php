<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'color',
        'text_color',
        'meeting_link',
        'is_appointment',
        'shared_with'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'shared_with' => 'array',
        'is_appointment' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
