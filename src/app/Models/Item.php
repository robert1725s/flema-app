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
}
