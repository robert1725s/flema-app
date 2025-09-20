<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 商品詳細ページで必要な情報が表示されるかテスト
     * テスト項目：商品画像、商品名、ブランド名、価格、いいね数、コメント数、
     * 商品説明、商品情報（カテゴリ、商品の状態）、コメント数、
     * コメントしたユーザー情報、コメント内容
     *
     * @return void
     */
    public function test_item_detail_page_displays_required_information()
    {
        // テストユーザーを作成
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        $commenter = User::create([
            'name' => 'コメントユーザー',
            'email' => 'commenter@example.com',
            'password' => Hash::make('password123'),
            'image_path' => 'user/commenter-profile.jpg',
        ]);

        $viewer = User::create([
            'name' => '閲覧者',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password123'),
        ]);

        // カテゴリを作成
        $category1 = Category::create(['content' => '家電']);
        $category2 = Category::create(['content' => 'ファッション']);

        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の詳細説明です。',
            'image_path' => 'items/test-item.jpg',
            'price' => 15000,
            'condition' => 2, // 目立った傷や汚れなし
            'seller_id' => $seller->id,
        ]);

        // 商品にカテゴリを関連付け
        $item->categories()->attach([$category1->id, $category2->id]);

        // お気に入りを作成
        Favorite::create([
            'user_id' => $viewer->id,
            'item_id' => $item->id,
        ]);

        // コメントを作成
        Comment::create([
            'content' => 'この商品について質問があります。',
            'user_id' => $commenter->id,
            'item_id' => $item->id,
        ]);

        Comment::create([
            'content' => '状態はいかがですか？',
            'user_id' => $viewer->id,
            'item_id' => $item->id,
        ]);

        // 閲覧者としてログイン
        $this->actingAs($viewer);

        // 商品詳細ページにアクセス
        $response = $this->get("/item/{$item->id}");
        $response->assertStatus(200);

        // 商品画像の表示確認
        $response->assertSee('items/test-item.jpg');

        // 商品名の表示確認
        $response->assertSee('テスト商品');

        // ブランド名の表示確認
        $response->assertSee('テストブランド');

        // 価格の表示確認
        $response->assertSee('15,000');
        $response->assertSee('(税込)');

        // いいね数の表示確認（1件のお気に入り）
        $response->assertSee('<span class="detail__action-count">1</span>', false);

        // コメント数の表示確認（2件のコメント）
        $response->assertSee('コメント(2)');

        // 商品説明の表示確認
        $response->assertSee('これはテスト商品の詳細説明です。');

        // 商品情報の表示確認
        $response->assertSee('商品の情報');

        // カテゴリの表示確認
        $response->assertSee('家電');
        $response->assertSee('ファッション');

        // 商品の状態の表示確認
        $response->assertSee('目立った傷や汚れなし');

        // コメントしたユーザー情報とコメント内容の表示確認
        $response->assertSee('コメントユーザー');
        $response->assertSee('この商品について質問があります。');
        $response->assertSee('状態はいかがですか？');

        // コメントユーザーのプロフィール画像確認
        $response->assertSee('user/commenter-profile.jpg');
    }

    /**
     * 複数選択されたカテゴリが表示されているかテスト
     *
     * @return void
     */
    public function test_item_detail_page_displays_multiple_categories()
    {
        // テストユーザーを作成
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        $viewer = User::create([
            'name' => '閲覧者',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 複数のカテゴリを作成
        $categories = [
            Category::create(['content' => 'メンズ']),
            Category::create(['content' => 'トップス']),
            Category::create(['content' => 'Tシャツ']),
            Category::create(['content' => 'カジュアル']),
        ];

        // 商品を作成
        $item = Item::create([
            'name' => 'カジュアルTシャツ',
            'brand' => 'テストブランド',
            'description' => 'カジュアルなTシャツです。',
            'image_path' => 'items/tshirt.jpg',
            'price' => 2500,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        // 商品に複数のカテゴリを関連付け
        $categoryIds = collect($categories)->pluck('id')->toArray();
        $item->categories()->attach($categoryIds);

        // 閲覧者としてログイン
        $this->actingAs($viewer);

        // 商品詳細ページにアクセス
        $response = $this->get("/item/{$item->id}");
        $response->assertStatus(200);

        // すべてのカテゴリが表示されていることを確認
        foreach ($categories as $category) {
            $response->assertSee($category->content);
        }
    }
}
