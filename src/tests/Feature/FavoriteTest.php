<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class FavoriteTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * いいねアイコンを押下することによって、いいねした商品として登録することができる。
     * 1. ユーザーにログインする
     * 2. 商品詳細ページを開く
     * 3. いいねアイコンを押下
     */
    public function test_user_can_favorite_item_by_clicking_icon()
    {
        // ユーザーを作成
        $user = $this->createVerifiedUser();

        // 出品者と商品を作成
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. 商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");
        $response->assertStatus(200);

        // 初期状態のいいね数を確認（0件）
        $html = $response->getContent();
        preg_match('/<button[^>]*detail__action-button--favorite[^>]*>.*?<span[^>]*detail__action-count[^>]*>(\d+)<\/span>/s', $html, $matches);
        $this->assertEquals('0', $matches[1] ?? '0');

        // 3. いいねアイコンを押下
        $response = $this->from("/item/{$item->id}")->post("/item/favorite/{$item->id}");

        // いいねした商品として登録され、いいね合計値が増加表示される
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // リダイレクト後のページを確認
        $response = $this->get("/item/{$item->id}");
        $html = $response->getContent();
        preg_match('/<button[^>]*detail__action-button--favorite[^>]*>.*?<span[^>]*detail__action-count[^>]*>(\d+)<\/span>/s', $html, $matches);
        $this->assertEquals('1', $matches[1] ?? '0', 'いいね数が1に増加していること');
    }

    /**
     * 追加済みのアイコンは色が変化する
     * 1. ユーザーにログインする
     * 2. 商品詳細ページを開く
     * 3. いいねアイコンを押下
     */
    public function test_favorited_icon_changes_color()
    {
        // ユーザーを作成
        $user = $this->createVerifiedUser();

        // 出品者と商品を作成
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. 商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");

        // 初期状態では空の星アイコンのクラスが含まれる
        $response->assertSee('far fa-star', false);
        $response->assertDontSee('fas fa-star', false);
        $response->assertDontSee('detail__action-icon--active', false);

        // 3. いいねアイコンを押下
        $this->from("/item/{$item->id}")->post("/item/favorite/{$item->id}");

        // いいね押下後のページを再取得
        $response = $this->get("/item/{$item->id}");

        // いいねアイコンが押下された状態では色が変化する（塗りつぶし星とアクティブクラス）
        $response->assertSee('fas fa-star', false);
        $response->assertSee('detail__action-icon--active', false);
        $response->assertDontSee('far fa-star', false);
    }

    /**
     * 再度いいねアイコンを押下することによって、いいねを解除することができる
     * 1. ユーザーにログインする
     * 2. 商品詳細ページを開く
     * 3. いいねアイコンを押下
     */
    public function test_user_can_unfavorite_item_by_clicking_icon_again()
    {
        // ユーザーを作成
        $user = $this->createVerifiedUser();

        // 出品者と商品を作成
        $seller = $this->createSeller();
        $item = $this->createItem($seller, [
            'name' => 'テスト商品',
        ]);

        // 既にいいね済みの状態にする
        $this->createFavorite($user, $item);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. 商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");

        // いいね数が1であることを確認
        $html = $response->getContent();
        preg_match('/<button[^>]*detail__action-button--favorite[^>]*>.*?<span[^>]*detail__action-count[^>]*>(\d+)<\/span>/s', $html, $matches);
        $this->assertEquals('1', $matches[1] ?? '0');

        // 3. いいねアイコンを押下（解除）
        $response = $this->from("/item/{$item->id}")->post("/item/favorite/{$item->id}");

        // いいねが解除され、いいね合計値が減少表示される
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $html = $response->getContent();
        preg_match('/<button[^>]*detail__action-button--favorite[^>]*>.*?<span[^>]*detail__action-count[^>]*>(\d+)<\/span>/s', $html, $matches);
        $this->assertEquals('0', $matches[1] ?? '0');
    }
}
