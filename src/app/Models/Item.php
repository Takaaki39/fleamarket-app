<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'price', 'brand_name', 'description', 'img_url', 'condition'];

    // {{ $item->condition_label }}で使う
    public function getConditionLabelAttribute()
    {
        return match ($this->condition) {
            1 => '良好',
            2 => '目立った傷や汚れ無し',
            3 => 'やや傷や汚れあり',
            4  => '状態が悪い',
            default => '不明',
        };
    }

    // 価格を3桁区切りにした文字列を返すアクセサ
    public function getPriceLabelAttribute()
    {
        return number_format($this->price);
    }
    
    public function stars()
    {
        return $this->hasMany(ItemStar::class, 'item_id');
    }

    // starの数を返すアクセサ
    public function getStarCountAttribute()
    {
        return $this->stars()->count();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_categories', 'item_id', 'category_id');
    }
    
    public function comments()
    {
        return $this->hasmany(ItemComment::class, 'item_id');
    }

    public function getCommentCountAttribute()
    {
        return $this->comments()->count();
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    
    public function sells()
    {
        return $this->hasMany(Sell::class);
    }

    // img_url のアクセサ
    public function getImgUrlAttribute($value)
    {
        // 値が null の場合はデフォルト画像でも可（任意）
        if (!$value) {
            return asset('images/noimage.png');
        }

        // https から始まる場合（外部URL）
        if (str_starts_with($value, 'https://') || str_starts_with($value, 'http://')) {
            return $value;
        }

        // storage 内のパス（例: "images/items/xxx.jpg"）
        if (str_starts_with($value, 'images')) {
            return asset('storage/' . $value);
        }

        // 念のためデフォルト
        return asset('storage/images/' . $value);
    }
    
}
