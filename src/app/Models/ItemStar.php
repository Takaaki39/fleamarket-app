<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStar extends Model
{
    use HasFactory;

    protected $table = 'item_stars';

    protected $fillable = [
        'user_id',
        'item_id',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
