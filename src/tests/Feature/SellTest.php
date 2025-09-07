<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class SellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用のストレージを使用
        Storage::fake('public');
    }

    /**
     * 正常な出品のテスト
     *
     * @return void
     */
    public function test_successful_sell()
    {
        // テスト用のユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // テスト用のカテゴリーを作成
        $category = Category::create([
            'content' => 'テストカテゴリー'
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // テスト用の画像ファイルを作成
        $image = UploadedFile::fake()->create('test_image.jpg', 100, 'image/jpeg');

        // 出品データ
        $sellData = [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の説明です',
            'image' => $image,
            'categories' => [$category->id],
            'condition' => '1',
            'price' => '1000',
        ];

        // 出品ページにPOSTリクエストを送信
        $response = $this->post('/sell', $sellData);

        // データベースに商品が作成されたことを確認
        $this->assertDatabaseHas('items', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の説明です',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $user->id,
        ]);

        // 画像ファイルが保存されたことを確認
        $item = Item::where('name', 'テスト商品')->first();
        $this->assertNotNull($item->image_path);
        $this->assertTrue(Storage::disk('public')->exists($item->image_path));

        // カテゴリーが関連付けられたことを確認
        $this->assertTrue($item->categories->contains($category));
    }
}
