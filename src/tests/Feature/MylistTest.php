<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class MylistTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * いいねした商品だけが表示される
     * 1. ユーザーにログインをする
     * 2. マイリストページを開く
     */
    public function test_only_favorited_items_are_displayed()
    {
        // ユーザーを作成
        $user = $this->createVerifiedUser();

        // 出品者を作成
        $seller = $this->createSeller();

        // 商品を作成
        $favoritedItem = $this->createItem($seller, [
            'name' => 'いいねした商品',
        ]);
        $this->createItem($seller, [
            'name' => 'いいねしていない商品',
        ]);

        // いいねを作成
        $this->createFavorite($user, $favoritedItem);

        // 1. ユーザーにログインをする
        $this->actingAs($user);

        // 2. マイリストページを開く
        $response = $this->get('/?tab=mylist');

        // いいねをした商品が表示される
        $response->assertSee('いいねした商品');

        // いいねしていない商品は表示されない
        $response->assertDontSee('いいねしていない商品');
    }

    /**
     * 購入済み商品は「Sold」と表示される
     * 1. ユーザーにログインをする
     * 2. マイリストページを開く
     * 3. 購入済み商品を確認する
     */
    public function test_sold_items_display_sold_label()
    {
        // ユーザーと出品者と購入者を作成
        $user = $this->createVerifiedUser();
        $seller = $this->createSeller();
        $buyer = $this->createBuyer();

        // 購入済み商品を作成
        $soldItem = $this->createSoldItem($seller, $buyer, [
            'name' => '購入済み商品',
        ]);

        // ユーザーがその商品をいいね
        $this->createFavorite($user, $soldItem);

        // 1. ユーザーにログインをする
        $this->actingAs($user);

        // 2. マイリストページを開く
        $response = $this->get('/?tab=mylist');

        // 3. 購入済み商品を確認する
        $response->assertSee('購入済み商品');

        // 購入済み商品に「Sold」のラベルが表示される
        $response->assertSee('SOLD');
    }

    /**
     * 未認証の場合は何も表示されない
     * 1. マイリストページを開く
     */
    public function test_unauthenticated_user_sees_nothing()
    {
        // 出品者を作成
        $seller = $this->createSeller();

        // 商品を作成
        $item = $this->createItem($seller, [
            'name' => 'テスト商品',
        ]);

        // 商品ページを開く
        $response = $this->get('/');

        // 出品商品が表示される
        $response->assertSee('テスト商品');

        // 1. マイリストページを開く（未認証）
        $response = $this->get('/?tab=mylist');

        // 何も表示されない（商品が表示されない）
        $response->assertDontSee('テスト商品');
    }
}
