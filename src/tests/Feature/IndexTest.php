<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 商品一覧取得：全商品を取得できる
     *
     * @return void
     */
    public function test_can_get_all_items()
    {
        // テスト用のユーザーを作成
        $user1 = User::create([
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::create([
            'name' => 'ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        // user1としてログイン
        $this->actingAs($user1);

        // user2が出品した商品を3つ作成
        Item::factory()->count(3)->create(['seller_id' => $user2->id]);

        // インデックスページにアクセス
        $response = $this->get('/');

        // 商品データが渡されていることを確認
        $response->assertViewHas('items');
        $items = $response->viewData('items');
        $this->assertCount(3, $items);
    }

    /**
     * 商品一覧取得：購入済み商品は「Sold」と表示される
     *
     * @return void
     */
    public function test_sold_items_display_sold_label()
    {
        // テスト用のユーザーを作成
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);
        $buyer = User::create([
            'name' => '購入者',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
        ]);
        $viewer = User::create([
            'name' => '閲覧者',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password123'),
        ]);

        // viewerとしてログイン
        $this->actingAs($viewer);

        // 販売中の商品を作成
        Item::create([
            'name' => '販売中商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/available.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $seller->id,
            'purchaser_id' => null, // 購入者なし
        ]);

        // 売却済み商品を作成
        Item::create([
            'name' => '売却済み商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/sold.jpg',
            'price' => 2000,
            'condition' => 1,
            'seller_id' => $seller->id,
            'purchaser_id' => $buyer->id, // 購入者あり
        ]);

        // インデックスページにアクセス
        $response = $this->get('/');

        // 売却済み商品には「SOLD」ラベルが表示されることを確認
        $response->assertSee('SOLD');
    }

    /**
     * 商品一覧取得：自分が出品した商品は表示されない
     *
     * @return void
     */
    public function test_own_items_are_not_displayed()
    {
        // テスト用のユーザーを作成
        $user1 = User::create([
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user2 = User::create([
            'name' => 'ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        // user1としてログイン
        $this->actingAs($user1);

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

        // インデックスページにアクセス
        $response = $this->get('/');

        // 他人の商品は表示される
        $response->assertSee('他人の商品');

        // 自分の商品は表示されない
        $response->assertDontSee('自分の商品');
    }

    /**
     * マイリスト一覧取得：自分がfavoriteした商品だけが表示される
     *
     * @return void
     */
    public function test_mylist_shows_only_favorited_items()
    {
        // テスト用のユーザーを作成
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // userとしてログイン
        $this->actingAs($user);

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

        // マイリストページにアクセス
        $response = $this->get('/?tab=mylist');

        // いいねした商品のみ表示される
        $response->assertSee('いいねした商品');
        $response->assertDontSee('通常商品');
    }

    /**
     * マイリスト一覧取得：購入済み商品は「Sold」と表示される
     *
     * @return void
     */
    public function test_mylist_sold_items_display_sold_label()
    {
        // テスト用のユーザーを作成
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);
        $buyer = User::create([
            'name' => '購入者',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
        ]);

        // userとしてログイン
        $this->actingAs($user);

        // 売却済みの商品を作成
        $soldItem = Item::create([
            'name' => '売却済みお気に入り商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/sold_favorite.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $seller->id,
            'purchaser_id' => $buyer->id,
        ]);

        // お気に入りに追加
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $soldItem->id,
        ]);

        // マイリストページにアクセス
        $response = $this->get('/?tab=mylist');

        // 商品名が表示され、SOLDラベルも表示される
        $response->assertSee('売却済みお気に入り商品');
        $response->assertSee('SOLD');
    }

    /**
     * マイリスト一覧取得：未認証の場合は何も表示されない
     *
     * @return void
     */
    public function test_mylist_shows_nothing_when_unauthenticated()
    {
        // 商品を作成
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);
        Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/test.jpg',
            'price' => 1000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        // 未ログイン状態でマイリストページにアクセス
        $response = $this->get('/?tab=mylist');

        // 商品は表示されない
        $response->assertDontSee('テスト商品');

        // ページ自体は正常に表示される
        $response->assertStatus(200);
    }

    /**
     * 商品検索機能：「商品名」で部分一致検索ができる
     *
     * @return void
     */
    public function test_can_search_items_by_name()
    {
        // テスト用のユーザーを作成
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // userとしてログイン
        $this->actingAs($user);

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

        // 「iPhone」で検索
        $response = $this->get('/?search=iPhone');

        // iPhone商品のみ表示される
        $response->assertSee('iPhone 14');
        $response->assertDontSee('Android スマートフォン');
    }

    /**
     * 商品検索機能：検索状態がマイリストでも保持されている
     *
     * @return void
     */
    public function test_search_state_maintained_in_mylist()
    {
        // テスト用のユーザーを作成
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // userとしてログイン
        $this->actingAs($user);

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

        // マイリストで「iPhone」を検索
        $response = $this->get('/?search=iPhone&tab=mylist');

        // iPhone商品のみ表示される
        $response->assertSee('iPhone 14');
        $response->assertDontSee('Android スマートフォン');
    }
}
