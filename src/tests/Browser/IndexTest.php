<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;

class IndexTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 商品一覧取得：全商品を取得できる
     */
    public function test_can_get_all_items()
    {
        // テスト用のユーザーを作成
        $user1 = User::create([
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123')
        ]);
        $user1->email_verified_at = now();
        $user1->save();

        $user2 = User::create([
            'name' => 'ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123')
        ]);

        // user2が出品した商品を3つ作成
        $items = [
            Item::create([
                'name' => '商品1',
                'description' => 'テスト商品1',
                'image_path' => 'items/test1.jpg',
                'price' => 1000,
                'condition' => 1,
                'seller_id' => $user2->id,
            ]),
            Item::create([
                'name' => '商品2',
                'description' => 'テスト商品2',
                'image_path' => 'items/test2.jpg',
                'price' => 2000,
                'condition' => 2,
                'seller_id' => $user2->id,
            ]),
            Item::create([
                'name' => '商品3',
                'description' => 'テスト商品3',
                'image_path' => 'items/test3.jpg',
                'price' => 3000,
                'condition' => 3,
                'seller_id' => $user2->id,
            ])
        ];

        $this->browse(function (Browser $browser) {
            // user1としてログイン
            $browser->visit('/login')
                ->type('[name="email"]', 'user1@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 商品一覧ページで3つの商品全てが表示されていることを確認
            $browser->visit('/')
                ->assertSee('商品1')
                ->assertSee('商品2')
                ->assertSee('商品3');
        });
    }

    /**
     * 商品一覧取得：購入済み商品は「Sold」と表示される
     */
    public function test_sold_items_display_sold_label()
    {
        // テスト用のユーザーを作成
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

        $viewer = User::create([
            'name' => '閲覧者',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password123')
        ]);
        $viewer->email_verified_at = now();
        $viewer->save();

        // 売却済み商品を作成
        Item::create([
            'name' => '売却済み商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/sold.jpg',
            'price' => 2000,
            'condition' => 1,
            'seller_id' => $seller->id,
            'purchaser_id' => $buyer->id,
        ]);

        $this->browse(function (Browser $browser) {
            // viewerとしてログイン
            $browser->visit('/login')
                ->type('[name="email"]', 'viewer@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 商品一覧ページで売却済みラベルを確認
            $browser->visit('/')
                ->assertSee('SOLD');
        });
    }

    /**
     * 商品一覧取得：自分が出品した商品は表示されない
     */
    public function test_own_items_are_not_displayed()
    {
        // テスト用のユーザーを作成
        $user1 = User::create([
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123')
        ]);
        $user1->email_verified_at = now();
        $user1->save();

        $user2 = User::create([
            'name' => 'ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123')
        ]);

        // user1の出品商品を作成
        Item::create([
            'name' => '自分の商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/own.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $user1->id,
        ]);

        // user2の出品商品を作成
        Item::create([
            'name' => '他人の商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/other.jpg',
            'price' => 2000,
            'condition' => 1,
            'seller_id' => $user2->id,
        ]);

        $this->browse(function (Browser $browser) {
            // user1としてログイン
            $browser->visit('/login')
                ->type('[name="email"]', 'user1@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 商品一覧ページで確認
            $browser->visit('/')
                ->assertSee('他人の商品')
                ->assertDontSee('自分の商品');
        });
    }
}
