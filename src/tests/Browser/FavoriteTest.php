<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class FavoriteTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * いいねアイコンを押下することによって、いいねした商品として登録することができる
     * 1. ユーザーにログインする
     * 2. 商品詳細ページを開く
     * 3. いいねアイコンを押下
     *
     * @return void
     */
    public function test_user_can_favorite_item_by_clicking_icon()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト商品の説明',
            'image_path' => 'items/test.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) use ($user, $item) {
            // 1. ユーザーにログインする
            $browser->loginAs($user)
                // 2. 商品詳細ページを開く
                ->visit("/item/{$item->id}")
                ->assertSee('テスト商品')
                ->assertSee('テストブランド')
                // 初期状態ではいいね数が0
                ->assertSeeIn('.detail__action-count', '0')
                // 空の星アイコンが表示されている
                ->assertPresent('.far.fa-star')
                // 3. いいねアイコンを押下
                ->click('.detail__action-button--favorite')
                ->waitForReload()
                // いいね後の状態を確認
                ->assertSeeIn('.detail__action-count', '1')
                // 塗りつぶされた星アイコンが表示されている
                ->assertPresent('.fas.fa-star')
                ->assertPresent('.detail__action-icon--active');
        });

        // データベースにお気に入りが登録されていることを確認
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * 追加済みのアイコンは色が変化する
     * 1. ユーザーにログインする
     * 2. 商品詳細ページを開く
     * 3. いいねアイコンを押下
     *
     * @return void
     */
    public function test_favorited_icon_changes_color()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト商品の説明',
            'image_path' => 'items/test.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) use ($user, $item) {
            // 1. ユーザーにログインする
            $browser->loginAs($user)
                // 2. 商品詳細ページを開く
                ->visit("/item/{$item->id}")
                // 初期状態では空の星アイコンが表示されている
                ->assertPresent('.far.fa-star')
                ->assertMissing('.fas.fa-star')
                ->assertMissing('.detail__action-icon--active')
                // 3. いいねアイコンを押下
                ->click('.detail__action-button--favorite')
                ->waitForReload()
                // アイコンの色が変化することを確認
                ->assertPresent('.fas.fa-star')
                ->assertPresent('.detail__action-icon--active')
                ->assertMissing('.far.fa-star');
        });
    }

    /**
     * 再度いいねアイコンを押下することによって、いいねを解除することができる
     * 1. ユーザーにログインする
     * 2. 商品詳細ページを開く
     * 3. いいねアイコンを押下（解除）
     *
     * @return void
     */
    public function test_user_can_unfavorite_item_by_clicking_icon_again()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト商品の説明',
            'image_path' => 'items/test.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        // あらかじめお気に入りに追加しておく
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->browse(function (Browser $browser) use ($user, $item) {
            // 1. ユーザーにログインする
            $browser->loginAs($user)
                // 2. 商品詳細ページを開く
                ->visit("/item/{$item->id}")
                // 初期状態ではお気に入り済み（塗りつぶし星）
                ->assertPresent('.fas.fa-star')
                ->assertPresent('.detail__action-icon--active')
                ->assertSeeIn('.detail__action-count', '1')
                // 3. いいねアイコンを押下（解除）
                ->click('.detail__action-button--favorite')
                ->waitForReload()
                // お気に入り解除後の状態を確認
                ->assertSeeIn('.detail__action-count', '0')
                ->assertPresent('.far.fa-star')
                ->assertMissing('.fas.fa-star')
                ->assertMissing('.detail__action-icon--active');
        });

        // データベースからお気に入りが削除されていることを確認
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }
}
