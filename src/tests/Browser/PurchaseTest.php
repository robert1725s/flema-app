<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PurchaseTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 「購入する」ボタンを押下すると購入が完了する
     *
     * 1. ユーザーにログインする
     * 2. 商品購入画面を開く
     * 3. 商品を選択して「購入する」ボタンを押下
     */
    public function test_purchase_completes_when_button_clicked()
    {
        // テストデータの準備
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        $this->browse(function (Browser $browser) use ($item, $buyer) {
            // 1. ユーザーにログインする
            $browser->visit('/login')
                ->type('[name="email"]', $buyer->email)
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 2. 商品購入画面を開く
            $browser->visit("/purchase/{$item->id}")
                ->assertSee($item->name)
                ->assertSee('¥ ' . number_format($item->price));

            // 3. 商品を選択して「購入する」ボタンを押下
            $browser->select('[name="payment_method"]', 'card')
                ->press('購入する');
        });

        // 購入が完了していることを確認（データベース確認）
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'purchaser_id' => $buyer->id,
        ]);
    }

    /**
     * 購入した商品は商品一覧画面にて「sold」と表示される
     *
     * 1. ユーザーにログインする
     * 2. 商品購入画面を開く
     * 3. 商品を選択して「購入する」ボタンを押下
     * 4. 商品一覧画面を表示する
     */
    public function test_purchased_item_shows_sold_on_list()
    {
        // テストデータの準備
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        $this->browse(function (Browser $browser) use ($item, $buyer) {
            // 1. ユーザーにログインする
            $browser->visit('/login')
                ->type('[name="email"]', $buyer->email)
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 2. 商品購入画面を開く
            $browser->visit("/purchase/{$item->id}")
                ->assertSee($item->name);

            // 3. 商品を選択して「購入する」ボタンを押下
            $browser->select('[name="payment_method"]', 'card')
                ->press('購入する');

            // 4. 商品一覧画面を表示する
            $browser->visit('/')
                ->assertSee($item->name);

            // 購入した商品が「SOLD」と表示されることを確認
            $browser->assertSee('SOLD');
        });

        // データベースでも購入完了を確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'purchaser_id' => $buyer->id,
        ]);
    }

    /**
     * 「プロフィール/購入した商品一覧」に追加されている
     *
     * 1. ユーザーにログインする
     * 2. 商品購入画面を開く
     * 3. 商品を選択して「購入する」ボタンを押下
     * 4. プロフィール画面を表示する
     */
    public function test_purchased_item_added_to_profile()
    {
        // テストデータの準備
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        $this->browse(function (Browser $browser) use ($item, $buyer) {
            // 1. ユーザーにログインする
            $browser->visit('/login')
                ->type('[name="email"]', $buyer->email)
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 2. 商品購入画面を開く
            $browser->visit("/purchase/{$item->id}")
                ->assertSee($item->name);

            // 3. 商品を選択して「購入する」ボタンを押下
            $browser->select('[name="payment_method"]', 'card')
                ->press('購入する');

            // 4. プロフィール画面を表示する
            $browser->visit('/mypage')
                ->clickLink('購入した商品');

            // 購入した商品が一覧に表示されることを確認
            $browser->assertSee($item->name);
        });

        // データベースでも購入完了を確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'purchaser_id' => $buyer->id,
        ]);
    }

    /**
     * 購入者ユーザーを作成
     */
    private function createBuyer()
    {
        $buyer = User::create([
            'name' => '購入者テスト',
            'email' => 'buyer.test@example.com',
            'password' => Hash::make('password123'),
            'postal_code' => '1234567',
            'address' => '東京都渋谷区テスト1-2-3',
            'building' => 'テストビル101',
        ]);
        $buyer->email_verified_at = now();
        $buyer->save();

        return $buyer;
    }

    /**
     * 出品者ユーザーを作成
     */
    private function createSeller()
    {
        return User::create([
            'name' => '出品者テスト',
            'email' => 'seller.test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * テスト用商品を作成
     */
    private function createItem($seller)
    {
        return Item::create([
            'name' => 'テスト商品_' . Str::random(5),
            'description' => 'これはテスト用の商品説明です',
            'image_path' => 'items/test.jpg',
            'price' => 5000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);
    }
}
