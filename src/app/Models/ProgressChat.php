<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressChat extends Model
{
    use HasFactory;
    protected $fillable = [
        'progress_id',
        'user_id',
        'message',
        'image',
        'is_customer',
    ];

    /**
     * 取引（Progress）
     */
    public function progress()
    {
        return $this->belongsTo(Progress::class);
    }

    /**
     * 投稿ユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
