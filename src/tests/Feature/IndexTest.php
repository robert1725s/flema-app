<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class IndexTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 全商品を取得できる
     * 1. 商品ページを開く
     */
    public function test_can_get_all_items()
    {
        // 出品者を作成
        $seller1 = $this->createSeller(['name' => '出品者1']);
        $seller2 = $this->createSeller(['name' => '出品者2']);

        // 複数の商品を作成
        $item1 = $this->createItem($seller1, [
            'name' => '商品1',
        ]);
        $item2 = $this->createItem($seller2, [
            'name' => '商品2',
        ]);

        // 1. 商品ページを開く
        $response = $this->get('/');

        // すべての商品が表示される
        $response->assertSee('商品1');
        $response->assertSee('商品2');
    }

    /**
     * 購入済み商品は「Sold」と表示される
     * 1. 商品ページを開く
     * 2. 購入済み商品を表示する
     */
    public function test_sold_items_display_sold_label()
    {
        // 出品者と購入者を作成
        $seller = $this->createSeller();
        $buyer = $this->createBuyer();

        // 購入済み商品を作成
        $soldItem = $this->createSoldItem($seller, $buyer, [
            'name' => '売却済み商品',
        ]);

        // 1.商品ページを開く
        $response = $this->get('/');

        // 商品が表示されることを確認
        $response->assertSee('売却済み商品');

        // 「SOLD」のラベルが表示される
        $response->assertSee('SOLD');
    }

    /**
     * 自分が出品した商品は表示されない
     * 1. ユーザーにログインをする
     * 2. 商品ページを開く
     */
    public function test_own_items_are_not_displayed()
    {
        // ユーザー（自分）を作成
        $user = $this->createVerifiedUser([
            'name' => '自分',
        ]);

        // 他人を作成
        $otherUser = $this->createSeller([
            'name' => '他人',
        ]);

        // 自分の商品を作成
        $myItem = $this->createItem($user, [
            'name' => '自分の商品',
        ]);

        // 他人の商品を作成
        $otherItem = $this->createItem($otherUser, [
            'name' => '他人の商品',
        ]);

        // 1. ユーザーにログインをする
        $this->actingAs($user);

        // 2. 商品ページを開く
        $response = $this->get('/');

        $response->assertStatus(200);

        // 自分が出品した商品が一覧に表示されない
        $response->assertDontSee('自分の商品');

        // 他人の商品は表示される
        $response->assertSee('他人の商品');
    }
}
