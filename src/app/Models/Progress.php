<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;
    protected $table = 'progresses';

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
        return $this->hasMany(ProgressChat::class);
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
