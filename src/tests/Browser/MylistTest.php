<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class MylistTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * マイリスト一覧取得：自分がfavoriteした商品だけが表示される
     */
    public function test_mylist_shows_only_favorited_items()
    {
        // テスト用のユーザーを作成
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123')
        ]);
        $user->email_verified_at = now();
        $user->save();

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123')
        ]);

        // 商品を2つ作成
        $item1 = Item::create([
            'name' => 'いいねした商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/favorite.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        Item::create([
            'name' => '通常商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/normal.jpg',
            'price' => 2000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        // item1のみをお気に入りに追加
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $item1->id,
        ]);

        $this->browse(function (Browser $browser) {
            // userとしてログイン
            $browser->visit('/login')
                ->type('[name="email"]', 'user@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // マイリストタブをクリックして確認
            $browser->visit('/')
                ->clickLink('マイリスト') // マイリストタブをクリック
                ->assertSee('いいねした商品')
                ->assertDontSee('通常商品');
        });
    }

    /**
     * マイリスト一覧取得：購入済み商品は「Sold」と表示される
     */
    public function test_mylist_sold_items_display_sold_label()
    {
        // テスト用のユーザーを作成
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123')
        ]);
        $user->email_verified_at = now();
        $user->save();

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123')
        ]);

        $buyer = User::create([
            'name' => '購入者',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123')
        ]);

        // 売却済みの商品を作成
        $soldItem = Item::create([
            'name' => '売却済みお気に入り商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/sold_favorite.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $seller->id,
            'purchaser_id' => $buyer->id, // 購入者設定
        ]);

        // お気に入りに追加
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $soldItem->id,
        ]);

        $this->browse(function (Browser $browser) {
            // userとしてログイン
            $browser->visit('/login')
                ->type('[name="email"]', 'user@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // マイリストタブをクリックして確認
            $browser->visit('/')
                ->clickLink('マイリスト')
                ->assertSee('売却済みお気に入り商品')
                ->assertSee('SOLD');
        });
    }

    /**
     * マイリスト一覧取得：未認証の場合は何も表示されない
     */
    public function test_mylist_shows_nothing_when_unauthenticated()
    {
        // 商品を作成
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123')
        ]);

        Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/test.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) {
            // 未ログイン状態でマイリストタブアクセス
            $browser->visit('/')
                ->clickLink('マイリスト')
                ->assertDontSee('テスト商品')
                ->assertPathIs('/'); // ページ自体は表示される
        });
    }
}