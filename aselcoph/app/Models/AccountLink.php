<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_number',
        'owner_name',
        'validated_at',
        'validated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}