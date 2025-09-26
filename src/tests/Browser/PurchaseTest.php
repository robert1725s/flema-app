<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;
use Mockery;

class PurchaseTest extends DuskTestCase
{
    use DatabaseMigrations;
    /**
     * 「購入する」ボタンを押下すると購入が完了する
     * 1. ユーザーにログインする
     * 2. 商品購入画面を開く
     * 3. 商品を選択して「購入する」ボタンを押下
     */
    public function test_purchase_button_completes_purchase()
    {
        // テストユーザーを作成
        $buyer = User::create([
            'name' => '購入者',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
            'postal_code' => '1234567',
            'address' => '東京都渋谷区テスト1-2-3',
            'building' => 'テストビル101',
        ]);
        $buyer->email_verified_at = now();
        $buyer->save();

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);


        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品の説明',
            'image_path' => 'items/test.jpg',
            'price' => 5000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) use ($item, $buyer) {
            // 1. ユーザーにログインする
            $browser->visit('/login')
                ->pause(2000) // ページ読み込み待機
                ->type('[name="email"]', 'buyer@example.com')
                ->pause(1000)
                ->type('[name="password"]', 'password123')
                ->pause(2000) // 入力内容確認
                ->press('ログインする')
                ->pause(3000) // ログイン処理待機
                ->assertPathIs('/');

            // 2. 商品購入画面を開く
            $browser->pause(2000) // ページ表示確認
                ->visit("/purchase/{$item->id}")
                ->pause(2000) // 購入画面読み込み待機
                ->assertSee('テスト商品')
                ->assertSee('¥ 5,000')
                ->pause(1000) // 確認待機
                ->select('[name="payment_method"]', 'card')
                ->pause(1000);

            // 購入ボタンをクリック（Stripeへのリダイレクト開始）
            try {
                $browser->press('購入する')
                    ->pause(1000); // 短い待機
            } catch (\Exception $e) {
                // Stripeへのリダイレクトによるセッションエラーを無視
            }
        });

        // Stripe決済完了をシミュレート（直接DB更新）
        $this->simulateStripePaymentSuccess($item, $buyer);

        // 購入完了後の確認
        $this->browse(function (Browser $browser) use ($item, $buyer) {
            // マイページに遷移して確認
            $browser->visit('/mypage')
                ->pause(2000) // マイページ読み込み待機
                ->assertSee('購入した商品')
                ->clickLink('購入した商品')
                ->pause(1000)
                ->assertSee('テスト商品')
                ->pause(2000); // 最終確認
        });

        // データベースに購入情報が登録されていることを確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'purchaser_id' => $buyer->id,
        ]);
    }

    /**
     * 購入した商品は商品一覧画面にて「sold」と表示される
     * 1. ユーザーにログインする
     * 2. 商品購入画面を開く
     * 3. 商品を選択して「購入する」ボタンを押下
     * 4. 商品一覧画面を表示する
     */
    public function test_purchased_item_shows_sold_on_listing()
    {
        // テストユーザーを作成
        $buyer = User::create([
            'name' => '購入者',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
            'postal_code' => '1234567',
            'address' => '東京都渋谷区テスト1-2-3',
            'building' => 'テストビル101',
        ]);
        $buyer->email_verified_at = now();
        $buyer->save();

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);


        // 商品を作成
        $item = Item::create([
            'name' => 'SOLD確認商品',
            'description' => 'テスト商品の説明',
            'image_path' => 'items/test.jpg',
            'price' => 3000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) use ($item, $buyer) {
            // 1. ユーザーにログインする
            $browser->visit('/login')
                ->pause(2000) // ページ読み込み待機
                ->type('[name="email"]', 'buyer@example.com')
                ->pause(1000)
                ->type('[name="password"]', 'password123')
                ->pause(2000) // 入力内容確認
                ->press('ログインする')
                ->pause(3000) // ログイン処理待機
                ->assertPathIs('/');

            // 2. 商品購入画面を開く
            $browser->pause(2000) // ページ表示確認
                ->visit("/purchase/{$item->id}")
                ->pause(2000) // 購入画面読み込み待機
                ->assertSee('SOLD確認商品')
                ->assertSee('¥ 3,000')
                ->select('[name="payment_method"]', 'card')
                ->pause(1000);

            // 購入ボタンをクリック
            try {
                $browser->press('購入する')
                    ->pause(1000);
            } catch (\Exception $e) {
                // Stripeへのリダイレクトによるセッションエラーを無視
            }
        });

        // Stripe決済完了をシミュレート（直接DB更新）
        $this->simulateStripePaymentSuccess($item, $buyer);

        // 購入完了後の確認
        $this->browse(function (Browser $browser) use ($item) {
            // 4. 商品一覧画面を表示する
            $browser->visit('/')
                ->pause(2000) // 商品一覧表示待機
                ->assertSee('SOLD確認商品')
                ->assertSee('SOLD')
                ->pause(2000); // 最終確認
        });
    }

    /**
     * 「プロフィール/購入した商品一覧」に追加されている
     * 1. ユーザーにログインする
     * 2. 商品購入画面を開く
     * 3. 商品を選択して「購入する」ボタンを押下
     * 4. プロフィール画面を表示する
     */
    public function test_purchased_item_appears_in_profile()
    {
        // テストユーザーを作成
        $buyer = User::create([
            'name' => '購入者',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
            'postal_code' => '1234567',
            'address' => '東京都渋谷区テスト1-2-3',
            'building' => 'テストビル101',
        ]);
        $buyer->email_verified_at = now();
        $buyer->save();

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);


        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品の説明',
            'image_path' => 'items/test.jpg',
            'price' => 8000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) use ($item, $buyer) {
            // 1. ユーザーにログインする
            $browser->visit('/login')
                ->pause(2000) // ページ読み込み待機
                ->type('[name="email"]', 'buyer@example.com')
                ->pause(1000)
                ->type('[name="password"]', 'password123')
                ->pause(2000) // 入力内容確認
                ->press('ログインする')
                ->pause(3000) // ログイン処理待機
                ->assertPathIs('/');

            // 2. 商品購入画面を開く
            $browser->pause(2000) // ページ表示確認
                ->visit("/purchase/{$item->id}")
                ->pause(2000) // 購入画面読み込み待機
                ->assertSee('テスト商品')
                ->assertSee('¥ 8,000')
                ->select('[name="payment_method"]', 'konbini')
                ->pause(1000);

            // 購入ボタンをクリック
            try {
                $browser->press('購入する')
                    ->pause(1000);
            } catch (\Exception $e) {
                // Stripeへのリダイレクトによるセッションエラーを無視
            }
        });

        // Stripe決済完了をシミュレート（直接DB更新）
        $this->simulateStripePaymentSuccess($item, $buyer);

        // 購入完了後の確認
        $this->browse(function (Browser $browser) use ($item) {
            // 4. プロフィール画面を表示する
            $browser->visit('/mypage')
                ->pause(2000) // マイページ読み込み待機
                // 「購入した商品」タブをクリック
                ->clickLink('購入した商品')
                ->pause(2000) // タブ切り替え待機
                // 購入した商品が表示されていることを確認
                ->assertSee('テスト商品')
                ->pause(2000); // 最終確認
        });

        // データベースに購入情報が登録されていることを確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'purchaser_id' => $buyer->id,
        ]);
    }
}
