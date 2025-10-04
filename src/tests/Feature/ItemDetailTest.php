<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class ItemDetailTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 必要な情報が表示される
     * （商品画像、商品名、ブランド名、価格、いいね数、コメント数、商品説明、商品情報（カテゴリ、商品の状態）、コメントしたユーザー情報、コメント内容)
     * 1. 商品詳細ページを開く
     */
    public function test_item_detail_page_displays_required_information()
    {
        // 出品者を作成
        $seller = $this->createSeller([
            'name' => '出品者テスト',
        ]);

        // 商品を作成
        $item = $this->createItem($seller, [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の詳細説明です。',
            'image_path' => 'items/test-item.jpg',
            'price' => 15000,
            'condition' => 1, // 目立った傷や汚れなし
        ]);

        // カテゴリを作成して商品に紐付け
        $category1 = $this->createCategory('家電');
        $category2 = $this->createCategory('ファッション');
        $item->categories()->attach([$category1->id, $category2->id]);

        // いいねを作成（1件）
        $favUser = $this->createVerifiedUser([
            'name' => 'いいねユーザー',
            'email' => 'fav@example.com',
        ]);
        $this->createFavorite($favUser, $item);

        // コメントを作成（2件）
        $commentUser1 = $this->createVerifiedUser([
            'name' => 'コメントユーザー1',
            'email' => 'comment1@example.com',
        ]);
        $commentUser2 = $this->createVerifiedUser([
            'name' => 'コメントユーザー2',
            'email' => 'comment2@example.com',
        ]);
        $this->createComment($commentUser1, $item, 'この商品について質問があります。');
        $this->createComment($commentUser2, $item, '状態はいかがですか？');

        // 1. 商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);

        // すべての情報が商品詳細ページに表示されている
        // 商品画像のパスが含まれることを確認
        $response->assertSee('storage/items/test-item.jpg', false);
        // 商品名の表示確認
        $response->assertSee('テスト商品');
        // ブランド名の表示確認
        $response->assertSee('テストブランド');
        // 価格の表示確認
        $response->assertSee('15,000');

        // いいね数とコメント数の表示確認
        // HTMLから詳細に確認
        $html = $response->getContent();

        // いいねボタンのカウント部分を抽出（detail__action-count）
        preg_match('/<button[^>]*detail__action-button--favorite[^>]*>.*?<span[^>]*detail__action-count[^>]*>(\d+)<\/span>/s', $html, $favoriteCountMatches);
        $this->assertEquals('1', $favoriteCountMatches[1] ?? '');

        // コメントボタンのカウント部分を抽出（detail__action-count）
        preg_match('/<button[^>]*detail__action-button--comment[^>]*>.*?<span[^>]*detail__action-count[^>]*>(\d+)<\/span>/s', $html, $commentCountMatches);
        $this->assertEquals('2', $commentCountMatches[1] ?? '');

        // コメントタイトルの確認
        $response->assertSee('コメント(2)');
        // 商品説明の表示確認
        $response->assertSee('これはテスト商品の詳細説明です。');
        // カテゴリの表示確認
        $response->assertSee('家電');
        $response->assertSee('ファッション');
        // 商品の状態の表示確認
        $response->assertSee('良好');
        // コメントしたユーザー情報とコメント内容の表示確認
        $response->assertSee('コメントユーザー1');
        $response->assertSee('この商品について質問があります。');
        $response->assertSee('コメントユーザー2');
        $response->assertSee('状態はいかがですか？');
    }

    /**
     * 複数選択されたカテゴリが表示されているか
     * 1. 商品詳細ページを開く
     */
    public function test_item_detail_page_displays_multiple_categories()
    {
        // 出品者を作成
        $seller = $this->createSeller();

        // 商品を作成
        $item = $this->createItem($seller, [
            'name' => 'マルチカテゴリ商品',
        ]);

        // 複数のカテゴリを作成して商品に紐付け
        $category1 = $this->createCategory('カテゴリ1');
        $category2 = $this->createCategory('カテゴリ2');
        $category3 = $this->createCategory('カテゴリ3');
        $item->categories()->attach([$category1->id, $category2->id, $category3->id]);

        // 1. 商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);

        // 複数選択されたカテゴリが商品詳細ページに表示されている
        $response->assertSee('カテゴリ1');
        $response->assertSee('カテゴリ2');
        $response->assertSee('カテゴリ3');
    }
}
