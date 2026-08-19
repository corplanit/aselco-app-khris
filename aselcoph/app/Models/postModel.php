<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class postModel extends Model
{
    use HasFactory;
    protected $table = 't_post';
    protected $primaryKey = 'post_id';
    protected $fillable = ['post_title', 'post_content', 'post_thumbnail', 'post_isActive', 'post_by', 'post_attachment', 'post_menu', 'isDeleted'];

    protected $casts = [
        'post_attachment' => 'array',
    ];

    public function author()
    {
          return $this->belongsTo(User::class, 'post_by');
    }
}
