<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_link_id',
        'file_path',
        'amount',
        'billing_date',
        'uploaded_by',
        'status'
    ];

    public function accountLink()
    {
        return $this->belongsTo(AccountLink::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

