<?php

namespace Database\Factories;

use App\Models\Favorite;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
        ];
    }

    /**
     * 既存のユーザーとアイテムからランダムに選択する
     */
    public function withExistingData()
    {
        return $this->state(function () {
            return [
                'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
                'item_id' => Item::inRandomOrder()->first()?->id ?? Item::factory(),
            ];
        });
    }
}