<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TAccountRaw extends Model
{
    protected $table = 't_accounts_raw'; // Make sure this matches your DB table name

    protected $primaryKey = 'account_no';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'account_no',
        'customer',
        'user_id',
        'status',
        'isDeleted',
    ];
}
