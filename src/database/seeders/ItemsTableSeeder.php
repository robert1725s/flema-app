<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $itemsData = [
            [
                'name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
                'condition' => 1,
                'seller_id' => 1,
                'filename' => 'clock.jpg',
                'categories' => [1, 4, 11], // ファッション, メンズ, アクセサリー
            ],
            [
                'name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
                'condition' => 2,
                'seller_id' => 2,
                'filename' => 'hdd_hard_disk.jpg',
                'categories' => [2], // 家電
            ],
            [
                'name' => '玉ねぎ3束',
                'price' => 300,
                'brand' => 'なし',
                'description' => '新鮮な玉ねぎ3束のセット',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
                'condition' => 3,
                'seller_id' => 1,
                'filename' => 'onions.jpg',
                'categories' => [9], // キッチン
            ],
            [
                'name' => '革靴',
                'price' => 4000,
                'brand' => '',
                'description' => 'クラシックなデザインの革靴',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
                'condition' => 4,
                'seller_id' => 2,
                'filename' => 'leather_shoes.jpg',
                'categories' => [1, 4], // ファッション, メンズ
            ],
            [
                'name' => 'ノートPC',
                'price' => 45000,
                'brand' => '',
                'description' => '高性能なノートパソコン',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
                'condition' => 1,
                'seller_id' => 1,
                'filename' => 'laptop.jpg',
                'categories' => [2], // 家電
            ],
            [
                'name' => 'マイク',
                'price' => 8000,
                'brand' => 'なし',
                'description' => '高音質のレコーディング用マイク',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
                'condition' => 2,
                'seller_id' => 2,
                'filename' => 'mic.jpg',
                'categories' => [2], // 家電
            ],
            [
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => '',
                'description' => 'おしゃれなショルダーバッグ',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
                'condition' => 3,
                'seller_id' => 1,
                'filename' => 'shoulder_bag.jpg',
                'categories' => [1], // ファッション
            ],
            [
                'name' => 'タンブラー',
                'price' => 500,
                'brand' => 'なし',
                'description' => '使いやすいタンブラー',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
                'condition' => 4,
                'seller_id' => 2,
                'filename' => 'tumbler.jpg',
                'categories' => [9], // キッチン
            ],
            [
                'name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
                'condition' => 1,
                'seller_id' => 1,
                'filename' => 'coffee_grinder.jpg',
                'categories' => [9, 3], // キッチン, インテリア
            ],
            [
                'name' => 'メイクセット',
                'price' => 2500,
                'brand' => '',
                'description' => '便利なメイクアップセット',
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
                'condition' => 2,
                'seller_id' => 2,
                'filename' => 'makeup_set.jpg',
                'categories' => [5], // コスメ
            ],
        ];

        $items = [];
        $itemCategories = [];

        foreach ($itemsData as $index => $itemData) {
            // 画像をダウンロード
            $response = Http::timeout(10)->get($itemData['img_url']);

            // storage/app/public/items に保存
            $imagePath = 'items/' . $itemData['filename'];
            Storage::disk('public')->put($imagePath, $response->body());

            // データベース用のアイテムデータを準備
            $items[] = [
                'name' => $itemData['name'],
                'price' => $itemData['price'],
                'brand' => $itemData['brand'],
                'description' => $itemData['description'],
                'image_path' => $imagePath,
                'condition' => $itemData['condition'],
                'seller_id' => $itemData['seller_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // カテゴリー関連付けデータを保存
            $itemCategories[$index] = $itemData['categories'];
        }

        // アイテムを個別に挿入してIDを取得
        $itemCategoryData = [];
        foreach ($items as $index => $itemData) {
            $itemId = DB::table('items')->insertGetId($itemData);

            // このアイテムのカテゴリー関連付けデータを作成
            foreach ($itemCategories[$index] as $categoryId) {
                $itemCategoryData[] = [
                    'item_id' => $itemId,
                    'category_id' => $categoryId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // カテゴリー関連付けを一括挿入
        if (!empty($itemCategoryData)) {
            DB::table('item_categories')->insert($itemCategoryData);
        }
    }
}
