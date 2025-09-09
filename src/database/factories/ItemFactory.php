<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Picsum画像のURLを生成
        $imageUrl = 'https://picsum.photos/640/480?random=' . $this->faker->numberBetween(1, 1000);

        // ストレージに保存するパスを生成
        $storagePath = 'items/' . Str::random(10) . '.jpg';

        // HTTPで画像を取得してstorageのpublicディスクに保存
        $response = Http::get($imageUrl);
        if ($response->successful()) {
            Storage::disk('public')->put($storagePath, $response->body());
        }

        return [
            'name' => $this->faker->words(3, true), // 商品名（3つの単語を組み合わせ）
            'brand' => $this->faker->optional()->company, // ブランド名（空の場合もあり）
            'description' => $this->faker->paragraph, // 商品説明
            'image_path' => $storagePath, // 画像パス
            'price' => $this->faker->numberBetween(100, 100000), // 価格（100円～100,000円）
            'condition' => $this->faker->numberBetween(1, 4), // 商品状態（1:良好 ～ 4:状態が悪い）
            'seller_id' => $this->faker->numberBetween(1, 4), // 出品者
            'purchaser_id' => null, // 購入者（初期はnull）
            'post_code' => null, // 郵便番号（初期はnull）
            'address' => null, // 住所（初期はnull）
            'building' => null, // 建物名（初期はnull）
        ];
    }
}
