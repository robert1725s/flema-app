<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;

class SellTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 商品出品画面にて必要な情報が保存できること
     * （カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格）
     * 1. ユーザーにログインする
     * 2. 商品出品画面を開く
     * 3. 各項目に適切な情報を入力して保存する
     */
    public function test_item_can_be_saved_with_required_information()
    {
        // テスト用のユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->email_verified_at = now();
        $user->save();

        // テスト用のカテゴリーを作成
        $categories = [
            Category::create(['content' => 'ファッション']),
            Category::create(['content' => 'メンズ']),
            Category::create(['content' => 'トップス']),
        ];

        $this->browse(function (Browser $browser) use ($user, $categories) {
            // 1. ユーザーにログインする
            $browser->loginAs($user);

            // 2. 商品出品画面を開く
            $browser->visit('/sell')
                ->assertSee('商品の出品');

            // 3. 各項目に適切な情報を入力して保存する

            // 商品画像をアップロード（テスト画像を作成またはスキップ）
            $testImagePath = base_path('tests/fixtures/test-image.jpg');
            if (!file_exists($testImagePath)) {
                // テスト画像がない場合はダミー画像を作成
                $testImageDir = dirname($testImagePath);
                if (!is_dir($testImageDir)) {
                    mkdir($testImageDir, 0755, true);
                }
                // 簡単な1x1ピクセルのダミー画像を作成
                $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
                file_put_contents($testImagePath, $imageData);
            }

            $browser->attach('[name="image"]', $testImagePath)

                // カテゴリを選択（複数選択） - ラベルをクリックする方法
                ->click(".sell__category-tag:nth-of-type(1)")
                ->click(".sell__category-tag:nth-of-type(2)")

                // 商品の状態を選択
                ->select('[name="condition"]', '1')

                // 商品名を入力
                ->type('[name="name"]', 'テスト商品名')

                // ブランド名を入力
                ->type('[name="brand"]', 'テストブランド')

                // 商品の説明を入力
                ->type('[name="description"]', 'これはテスト商品の詳細な説明文です。商品の特徴や状態について記載しています。')

                // 販売価格を入力
                ->type('[name="price"]', '15000')

                // 出品するボタンをクリック
                ->press('出品する')

                // 保存後の確認（実際の動作に合わせて/へリダイレクト）
                ->assertPathIs('/');
        });

        // データベースに商品が保存されたことを確認
        $this->assertDatabaseHas('items', [
            'name' => 'テスト商品名',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の詳細な説明文です。商品の特徴や状態について記載しています。',
            'price' => 15000,
            'condition' => 1,
            'seller_id' => $user->id,
        ]);

        // カテゴリーが正しく関連付けられたことを確認
        $item = Item::where('name', 'テスト商品名')->first();
        $this->assertNotNull($item);
        $this->assertEquals(2, $item->categories->count());
    }
}
