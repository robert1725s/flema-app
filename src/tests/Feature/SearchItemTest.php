<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class SearchItemTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 「商品名」で部分一致検索ができる
     * 1. 検索欄にキーワードを入力
     * 2. 検索ボタンを押す
     */
    public function test_can_search_items_by_partial_name_match()
    {
        // 出品者を作成
        $seller = $this->createSeller();

        // 商品を作成
        $this->createItem($seller, [
            'name' => 'テストスマートフォン',
        ]);
        $this->createItem($seller, [
            'name' => 'スマートウォッチ',
        ]);
        $this->createItem($seller, [
            'name' => 'ノートパソコン',
        ]);

        // 1. 検索欄にキーワードを入力して検索ボタンを押す
        $response = $this->get('/?search=スマート');

        // 部分一致する商品が表示される
        $response->assertSee('テストスマートフォン');
        $response->assertSee('スマートウォッチ');

        // 一致しない商品は表示されない
        $response->assertDontSee('ノートパソコン');
    }

    /**
     * 検索状態がマイリストでも保持されている
     * 1. ホームページで商品を検索
     * 2. 検索結果が表示される
     * 3. マイリストページに遷移
     */
    public function test_search_state_is_preserved_in_mylist()
    {
        // ユーザーを作成
        $user = $this->createVerifiedUser();

        // 出品者を作成
        $seller = $this->createSeller();

        // 商品を作成
        $item1 = $this->createItem($seller, [
            'name' => 'テストスマートフォン',
        ]);
        $item2 = $this->createItem($seller, [
            'name' => 'スマートウォッチ',
        ]);
        $item3 = $this->createItem($seller, [
            'name' => 'ノートパソコン',
        ]);

        // ユーザーが商品をいいね
        $this->createFavorite($user, $item1);
        $this->createFavorite($user, $item2);
        $this->createFavorite($user, $item3);

        // ユーザーにログイン
        $this->actingAs($user);

        // 1. ホームページで商品を検索
        $response = $this->from('/')->get('/?search=スマート');

        // 2. 検索結果が表示される
        $response->assertSee('テストスマートフォン');
        $response->assertSee('スマートウォッチ');
        $response->assertDontSee('ノートパソコン');

        // 3. マイリストページに遷移
        $response = $this->get('/?tab=mylist&search=スマート');

        // 検索キーワードが保持されている
        $response->assertSee('value="スマート"', false);

        // マイリストでも検索結果が反映されている
        $response->assertSee('テストスマートフォン');
        $response->assertSee('スマートウォッチ');
        $response->assertDontSee('ノートパソコン');
    }
}
