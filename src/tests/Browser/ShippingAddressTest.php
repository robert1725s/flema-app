<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class ShippingAddressTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
     * 1. ユーザーにログインする
     * 2. 送付先住所変更画面で住所を登録する
     * 3. 商品購入画面を再度開く
     */
    public function test_shipping_address_is_reflected_on_purchase_screen()
    {
        // テスト用ユーザーを作成
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
            'postal_code' => '111-1111',
            'address' => '東京都品川区1-1-1',
        ]);
        $seller->email_verified_at = now();
        $seller->save();

        $buyer = User::create([
            'name' => '購入者',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
            'postal_code' => '222-2222',
            'address' => '東京都渋谷区2-2-2',
            'building' => '元の建物名',
        ]);
        $buyer->email_verified_at = now();
        $buyer->save();

        // テスト用カテゴリを作成
        $category = Category::create(['content' => 'テストカテゴリ']);

        // テスト用商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品です',
            'price' => 10000,
            'condition' => 1,
            'image_path' => 'test.jpg',
            'seller_id' => $seller->id,
        ]);
        $item->categories()->attach($category->id);

        $this->browse(function (Browser $browser) use ($buyer, $item) {
            // 1. ユーザーにログインする
            $browser->loginAs($buyer);

            // 商品購入画面を開く
            $browser->visit('/purchase/' . $item->id);

            // 初期の住所が表示されていることを確認
            $browser->assertSee('東京都渋谷区2-2-2')
                ->assertSee('元の建物名');

            // 2. 送付先住所変更画面で住所を登録する
            $browser->clickLink('変更する')
                ->assertPathIs('/purchase/address/' . $item->id);

            // 新しい住所を入力
            $browser->clear('[name="postal_code"]')
                ->type('[name="postal_code"]', '333-3333')
                ->clear('[name="address"]')
                ->type('[name="address"]', '大阪府大阪市北区3-3-3')
                ->clear('[name="building"]')
                ->type('[name="building"]', '新しいマンション301号室')
                ->press('更新する');

            // 3. 商品購入画面を再度開く
            $browser->assertPathIs('/purchase/' . $item->id)
                ->assertSee('テスト商品');

            // 新しい住所が反映されていることを確認
            $browser->assertSee('333-3333')
                ->assertSee('大阪府大阪市北区3-3-3')
                ->assertSee('新しいマンション301号室');

            // 古い住所が表示されていないことを確認
            $browser->assertDontSee('222-2222')
                ->assertDontSee('東京都渋谷区2-2-2')
                ->assertDontSee('元の建物名');
        });
    }

    /**
     * 購入した商品に送付先住所が紐づいて登録される
     * 1. ユーザーにログインする
     * 2. 送付先住所変更画面で住所を登録する
     * 3. 商品を購入する
     */
    public function test_shipping_address_is_saved_with_purchased_item()
    {
        // テスト用ユーザーを作成
        $seller = User::create([
            'name' => '出品者太郎',
            'email' => 'seller2@example.com',
            'password' => Hash::make('password123'),
            'postal_code' => '444-4444',
            'address' => '福岡県福岡市中央区4-4-4',
        ]);

        $buyer = User::create([
            'name' => '購入者花子',
            'email' => 'buyer2@example.com',
            'password' => Hash::make('password123'),
            'postal_code' => '555-5555',
            'address' => '愛知県名古屋市中区5-5-5',
            'building' => 'デフォルトビル',
        ]);
        $buyer->email_verified_at = now();
        $buyer->save();

        // テスト用カテゴリを作成
        $category = Category::create(['content' => 'ファッション']);

        // テスト用商品を作成
        $item = Item::create([
            'name' => '購入テスト商品',
            'brand' => 'ブランドA',
            'description' => '購入テスト用の商品説明',
            'price' => 25000,
            'condition' => 2,
            'image_path' => 'test2.jpg',
            'seller_id' => $seller->id,
        ]);
        $item->categories()->attach($category->id);

        $this->browse(function (Browser $browser) use ($buyer, $item) {
            // 1. ユーザーにログインする
            $browser->loginAs($buyer);

            // 商品購入画面を開く
            $browser->visit('/purchase/' . $item->id)
                ->assertSee('購入テスト商品')
                ->assertSee('¥ 25,000');

            // 2. 送付先住所変更画面で住所を登録する
            $browser->clickLink('変更する')
                ->assertPathIs('/purchase/address/' . $item->id)
                ->assertSee('住所の変更');

            // 配送先住所を入力
            $browser->clear('[name="postal_code"]')
                ->type('[name="postal_code"]', '666-6666')
                ->clear('[name="address"]')
                ->type('[name="address"]', '北海道札幌市中央区6-6-6')
                ->clear('[name="building"]')
                ->type('[name="building"]', '配送先マンション606号室')
                ->press('更新する');

            // 購入画面に戻って住所が反映されていることを確認
            $browser->assertPathIs('/purchase/' . $item->id)
                ->assertSee('666-6666')
                ->assertSee('北海道札幌市中央区6-6-6')
                ->assertSee('配送先マンション606号室');

            // 支払い方法を選択
            $browser->select('[name="payment_method"]', 'card')
                ->press('購入する');
        });


        // データベースで購入情報と配送先住所を確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'purchaser_id' => $buyer->id,
            'postal_code' => '666-6666',
            'address' => '北海道札幌市中央区6-6-6',
            'building' => '配送先マンション606号室',
        ]);
    }
}
