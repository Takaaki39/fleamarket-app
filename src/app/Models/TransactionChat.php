<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionChat extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'user_id',
        'message',
        'image',
        'is_customer',
    ];

    /**
     * 取引（Transaction）
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * 投稿ユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
