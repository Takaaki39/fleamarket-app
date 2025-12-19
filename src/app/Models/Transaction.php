<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transactions';

    protected $fillable = [
        'item_id',
        'user_id',
        'customer_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function chats()
    {
        return $this->hasMany(TransactionChat::class);
    }

    /**
     * 未読メッセージ数
     */
    public function getUnreadCountAttribute()
    {
        if (!auth()->check()) {
            return 0;
        }

        return $this->chats()
            ->where('is_read', false)
            ->where('user_id', '!=', auth()->id())
            ->count();
    }
}
