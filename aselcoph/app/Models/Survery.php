<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survery extends Model
{
    use HasFactory;

    protected $table = 't_survery_link';
    protected $fillable = [
        'link',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}