<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class ItemDetailTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 商品詳細ページで必要な情報が表示されるかテスト
     * テスト項目：商品画像、商品名、ブランド名、価格、いいね数、コメント数、
     * 商品説明、商品情報（カテゴリ、商品の状態）、コメント数、
     * コメントしたユーザー情報、コメント内容
     */
    public function test_item_detail_page_displays_required_information()
    {
        // テストユーザーを作成
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        $commenter1 = User::create([
            'name' => 'コメントユーザー1',
            'email' => 'commenter1@example.com',
            'password' => Hash::make('password123'),
            'image_path' => 'user/commenter-profile.jpg',
        ]);

        $commenter2 = User::create([
            'name' => 'コメントユーザー2',
            'email' => 'commenter2@example.com',
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
            'user_id' => $commenter1->id,
            'item_id' => $item->id,
        ]);

        // コメントを作成
        Comment::create([
            'content' => 'この商品について質問があります。',
            'user_id' => $commenter1->id,
            'item_id' => $item->id,
        ]);

        Comment::create([
            'content' => '状態はいかがですか？',
            'user_id' => $commenter2->id,
            'item_id' => $item->id,
        ]);

        $this->browse(function (Browser $browser) use ($item) {
            // 商品詳細ページにアクセス
            $browser->visit("/item/{$item->id}")
                // 商品画像の表示確認
                ->assertPresent('.detail__image')
                ->assertSourceHas('storage/items/test-item.jpg')
                // 商品名の表示確認
                ->assertSee('テスト商品')
                // ブランド名の表示確認
                ->assertSee('テストブランド')
                // 価格の表示確認
                ->assertSee('15,000')
                // いいね数の表示確認（1件のお気に入り）
                ->assertSeeIn('.detail__action-button--favorite', '1')
                // コメント数(アイコン)の表示確認（2件のコメント）
                ->assertSeeIn('.detail__action-button--comment', '2')
                // コメント数(コメントセクション)の表示確認（2件のコメント）
                ->assertSee('コメント(2)')
                // 商品説明の表示確認
                ->assertSee('これはテスト商品の詳細説明です。')
                // 商品情報の表示確認
                ->assertSee('商品の情報')
                // カテゴリの表示確認
                ->assertSee('家電')
                ->assertSee('ファッション')
                // 商品の状態の表示確認
                ->assertSee('目立った傷や汚れなし')
                // コメントしたユーザー情報とコメント内容の表示確認
                ->assertSee('コメントユーザー1')
                ->assertSee('この商品について質問があります。')
                ->assertSee('コメントユーザー2')
                ->assertSee('状態はいかがですか？');
        });
    }

    /**
     * 複数選択されたカテゴリが表示されているかテスト
     */
    public function test_item_detail_page_displays_multiple_categories()
    {
        // テストユーザーを作成
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 複数のカテゴリを作成
        $categories = [
            Category::create(['content' => 'メンズ']),
            Category::create(['content' => 'ファッション']),
            Category::create(['content' => 'ハンドメイド']),
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

        $this->browse(function (Browser $browser) use ($item) {
            // 商品詳細ページにアクセス
            $browser->visit("/item/{$item->id}")
                // すべてのカテゴリが表示されていることを確認
                ->assertSee('メンズ')
                ->assertSee('ファッション')
                ->assertSee('ハンドメイド');
        });
    }
}
