<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;

class MypageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザー情報取得テスト：必要な情報が取得できる
     * （プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
     *
     * @return void
     */
    public function test_mypage_displays_user_information_correctly()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'image_path' => 'user/test-profile.jpg',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
        ]);

        // 出品した商品を作成
        $soldItem1 = Item::create([
            'name' => '出品商品1',
            'price' => 1000,
            'description' => 'テスト出品商品1の説明',
            'image_path' => 'items/sold1.jpg',
            'condition' => 1,
            'seller_id' => $user->id,
        ]);

        $soldItem2 = Item::create([
            'name' => '出品商品2',
            'price' => 2000,
            'description' => 'テスト出品商品2の説明',
            'image_path' => 'items/sold2.jpg',
            'condition' => 2,
            'seller_id' => $user->id,
        ]);

        // 他のユーザーが出品した商品（購入用）
        $otherUser = User::create([
            'name' => '他のユーザー',
            'email' => 'other@example.com',
            'password' => Hash::make('password456'),
        ]);

        // 購入した商品を作成
        $purchasedItem1 = Item::create([
            'name' => '購入商品1',
            'price' => 3000,
            'description' => 'テスト購入商品1の説明',
            'image_path' => 'items/purchased1.jpg',
            'condition' => 1,
            'seller_id' => $otherUser->id,
            'purchaser_id' => $user->id,
        ]);

        $purchasedItem2 = Item::create([
            'name' => '購入商品2',
            'price' => 4000,
            'description' => 'テスト購入商品2の説明',
            'image_path' => 'items/purchased2.jpg',
            'condition' => 2,
            'seller_id' => $otherUser->id,
            'purchaser_id' => $user->id,
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // マイページにアクセス（出品した商品タブ）
        $response = $this->get('/mypage');
        $response->assertStatus(200);

        // ユーザー名が表示されている
        $response->assertSee('テストユーザー');

        // プロフィール画像のパスが含まれている
        $response->assertSee('user/test-profile.jpg');

        // 出品した商品が表示されている
        $response->assertSee('出品商品1');
        $response->assertSee('出品商品2');

        // 購入した商品タブにアクセス
        $response = $this->get('/mypage?page=buy');
        $response->assertStatus(200);

        // 購入した商品が表示されている
        $response->assertSee('購入商品1');
        $response->assertSee('購入商品2');
    }
}
