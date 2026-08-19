<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FileManager extends Model
{
    use HasFactory;

    protected $table = 't_file_manager';

    protected $fillable = ['link', 'name', 'path', 'size', 'format', 'mime_type', 'user_id', 'parent_id', 'is_folder', 'isDeleted', 'google_drive_folder_id', 'google_drive_id', 'uploader_id'];

    public function children()
    {
        return $this->hasMany(FileManager::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(FileManager::class, 'parent_id');
    }
}
