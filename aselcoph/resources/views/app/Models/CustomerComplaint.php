<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerComplaint extends Model
{
    use HasFactory;

    protected $table = 'customer_complaints';
    
    protected $fillable = [
        'account_number',
        'name',
        'contact',
        'complaint',
        'attachment',
        'user_id',
        'status'
    ];
}
