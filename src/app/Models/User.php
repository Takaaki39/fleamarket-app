<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Item;

class User extends Authenticatable  implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * 購入したアイテム（purchases テーブル経由）
     */
    public function purchasedItems()
    {
        // purchases テーブルが user_id / item_id を持つ想定
        return $this->belongsToMany(Item::class, 'purchases', 'user_id', 'item_id')->withTimestamps();
    }

    public function sells()
    {
        return $this->hasMany(Sell::class);
    }

    /**
     * 出品した／売れたアイテム（sells テーブル経由）
     */
    public function selledItems()
    {
        // sells テーブルが user_id / item_id を持つ想定
        return $this->belongsToMany(Item::class, 'sells', 'user_id', 'item_id')->withTimestamps();
    }

    public function transactions()
    {
        // statusが2以外のものを取得
        return $this->hasMany(Transaction::class, 'user_id')->where('status', '!=', 2);
    }

    /**
     * idがTransactionsテーブルのcustomer_idと一致するデータを取得
     */
    public function customerTransactions()
    {
        // statusが1未満のものを取得
        return $this->hasMany(Transaction::class, 'customer_id')->whereNotIn('status', [1, 2]);
    }

    /**
     * transactionsとcustomerTransactionsの両方を統合して取得
     */
    public function allTransactions()
    {
        // 新規メッセージが来た順にする
        return Transaction::with(['chats' => function ($q) {
            $q->latest();
        }])
            ->where(function ($q) {
                // 出品者（user_id が自分）
                $q->where(function ($q) {
                    $q->where('user_id', $this->id)
                        ->where('status', '!=', 2);
                })
                    // 購入者（customer_id が自分）
                    ->orWhere(function ($q) {
                        $q->where('customer_id', $this->id)
                            ->where('status', 0);
                    });
            })
            ->get()
            ->sortByDesc(function ($transaction) {
                return optional($transaction->chats->first())->created_at;
            })
            ->values();
    }

    public function transactionChats()
    {
        return $this->hasMany(TransactionChat::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'user_id');
    }

    /**
     * 全取引の未読チャット数
     */
    public function getUnreadChatsAttribute()
    {
        return TransactionChat::where('is_read', false)
            ->where('user_id', '!=', $this->id) // 相手のメッセージのみ
            ->whereHas('transaction', function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('customer_id', $this->id);
            })
            ->count();
    }
}
