<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class SearchItemTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 商品検索機能：「商品名」で部分一致検索ができる
     */
    public function test_can_search_items_by_name()
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

        // 検索用の商品を作成
        Item::create([
            'name' => 'iPhone 14',
            'description' => 'テスト商品です',
            'image_path' => 'items/iphone.jpg',
            'price' => 100000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        Item::create([
            'name' => 'Android スマートフォン',
            'description' => 'テスト商品です',
            'image_path' => 'items/android.jpg',
            'price' => 50000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) {
            // userとしてログイン
            $browser->visit('/login')
                ->type('[name="email"]', 'user@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 「iPhone」で検索
            $browser->visit('/')
                ->type('[name="search"]', 'iPhone')
                ->click('.header__search-button')
                ->assertSee('iPhone 14')
                ->assertDontSee('Android スマートフォン');
        });
    }

    /**
     * 商品検索機能：検索状態がマイリストでも保持されている
     */
    public function test_search_state_maintained_in_mylist()
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

        // お気に入り商品を作成
        $item1 = Item::create([
            'name' => 'iPhone 14',
            'description' => 'テスト商品です',
            'image_path' => 'items/test1.jpg',
            'price' => 100000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $item2 = Item::create([
            'name' => 'Android スマートフォン',
            'description' => 'テスト商品です',
            'image_path' => 'items/test2.jpg',
            'price' => 50000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        // 両方をお気に入りに追加
        Favorite::create(['user_id' => $user->id, 'item_id' => $item1->id]);
        Favorite::create(['user_id' => $user->id, 'item_id' => $item2->id]);

        $this->browse(function (Browser $browser) {
            // userとしてログイン
            $browser->visit('/login')
                ->type('[name="email"]', 'user@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // マイリストタブに切り替えて「iPhone」で検索
            $browser->visit('/')
                ->clickLink('マイリスト') // マイリストタブ
                ->type('[name="search"]', 'iPhone')
                ->click('.header__search-button')
                ->assertSee('iPhone 14')
                ->assertDontSee('Android スマートフォン');
        });
    }
}
