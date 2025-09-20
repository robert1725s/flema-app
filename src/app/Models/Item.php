<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'description',
        'image_path',
        'price',
        'condition',
        'seller_id',
        'purchaser_id',
        'post_code',
        'address',
        'building',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_categories');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * 商品の出品者
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * 商品の購入者
     */
    public function purchaser()
    {
        return $this->belongsTo(User::class, 'purchaser_id');
    }

    /**
     * 商品へのコメント
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * ユーザーがこの商品をお気に入りしているか
     */
    public function isFavoritedBy($user)
    {
        if (!$user) {
            return false;
        }
        return $this->favorites()->where('user_id', $user->id)->exists();
    }
}
