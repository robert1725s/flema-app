<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Item;

class FavoritesTableSeeder extends Seeder
{
    public function run()
    {
        // ユーザーとアイテムを取得
        $users = User::all();
        $items = Item::all();

        // ユーザーとアイテムが存在しない場合は処理を終了
        if ($users->count() < 2 || $items->count() < 2) {
            return;
        }

        // ランダムに2人のユーザーを選択
        $selectedUsers = $users->random(2);

        // お気に入りデータを格納する配列
        $favorites = [];

        foreach ($selectedUsers as $user) {
            // 各ユーザーに対してランダムに2つのアイテムを選択
            $selectedItems = $items->random(min(2, $items->count()));

            foreach ($selectedItems as $item) {
                // 重複チェック（同じユーザーが同じアイテムをお気に入りしていないか）
                $exists = DB::table('favorites')
                    ->where('user_id', $user->id)
                    ->where('item_id', $item->id)
                    ->exists();

                if (!$exists) {
                    $favorites[] = [
                        'user_id' => $user->id,
                        'item_id' => $item->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // お気に入りデータを一括挿入
        if (!empty($favorites)) {
            DB::table('favorites')->insert($favorites);
        }
    }
}
