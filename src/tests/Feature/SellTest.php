<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class SellTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 商品出品画面にて必要な情報が保存できること（カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格）
     * 1. ユーザーにログインする
     * 2. 商品出品画面を開く
     * 3. 各項目に適切な情報を入力して保存する
     */
    public function test_item_can_be_created_with_all_required_information()
    {
        // ストレージのフェイクを設定
        Storage::fake('public');

        // ユーザーを作成
        $user = $this->createVerifiedUser();

        // カテゴリを作成
        $category1 = $this->createCategory('家電');
        $category2 = $this->createCategory('スマホ');

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. 商品出品画面を開く
        $response = $this->get('/sell');
        $response->assertStatus(200);

        // 3. 各項目に適切な情報を入力して保存する
        $image = UploadedFile::fake()->create('test-item.jpg', 100, 'image/jpeg');

        $response = $this->from('/sell')->post('/sell', [
            'image' => $image,
            'categories' => [$category1->id, $category2->id],
            'condition' => '1',
            'name' => 'テスト出品商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト用の商品説明です。',
            'price' => '15000',
        ]);

        // 各項目が正しく保存されている

        // データベースに商品が保存されていることを確認
        $this->assertDatabaseHas('items', [
            'seller_id' => $user->id,
            'name' => 'テスト出品商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト用の商品説明です。',
            'price' => 15000,
            'condition' => 1,
        ]);

        // 商品を取得
        $item = \App\Models\Item::where('name', 'テスト出品商品')->first();

        // 画像が保存されていることを確認
        $this->assertNotNull($item->image_path);
        Storage::disk('public')->assertExists($item->image_path);

        // カテゴリが正しく紐づいていることを確認
        $this->assertTrue($item->categories->contains($category1));
        $this->assertTrue($item->categories->contains($category2));
        $this->assertEquals(2, $item->categories->count());
    }
}
