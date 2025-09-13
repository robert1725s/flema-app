<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Favorite;
use App\Models\User;
use App\Models\Item;

class FavoritesTableSeeder extends Seeder
{
    public function run()
    {
        // 既存のユーザーとアイテムが存在する場合
        $users = User::all();
        $items = Item::all();

        if ($users->count() > 0 && $items->count() > 0) {
            // 既存データを使用してお気に入りを作成
            Favorite::factory()
                ->count(10)
                ->withExistingData()
                ->create();
        } else {
            // 新規データと一緒にお気に入りを作成
            Favorite::factory()
                ->count(10)
                ->create();
        }
    }
}
