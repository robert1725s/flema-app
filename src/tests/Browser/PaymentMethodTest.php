<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;

class PaymentMethodTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 小計画面で変更が反映される
     * 1. 支払い方法選択画面を開く
     * 2. プルダウンメニューから支払い方法を選択する
     *
     * @return void
     */
    public function test_payment_method_changes_are_reflected_in_summary()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
        ]);

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト商品の説明',
            'image_path' => 'items/test.jpg',
            'price' => 15000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) use ($user, $item) {
            // 1. 支払い方法選択画面を開く
            $browser->loginAs($user)
                ->visit("/purchase/{$item->id}")
                ->assertSee('テスト商品')
                ->assertSee('¥ 15,000')
                ->assertSee('支払い方法')
                // 初期状態では「選択してください」が表示されている
                ->assertSeeIn('.purchase__payment-display', '選択してください')
                ->assertSelected('.purchase__payment-select', '')
                // 2. プルダウンメニューからコンビニ払いを選択
                ->select('.purchase__payment-select', 'konbini')
                ->pause(500) // JavaScriptの処理を待つ
                // 小計画面での変更確認
                ->assertSeeIn('.purchase__payment-display', 'コンビニ払い')
                ->assertSelected('.purchase__payment-select', 'konbini')
                // 隠しフィールドにも値が設定されることを確認
                ->assertInputValue('.purchase__payment-hidden', 'konbini')
                // カード支払いに変更
                ->select('.purchase__payment-select', 'card')
                ->pause(500) // JavaScriptの処理を待つ
                // 小計画面での変更確認
                ->assertSeeIn('.purchase__payment-display', 'カード支払い')
                ->assertSelected('.purchase__payment-select', 'card')
                ->assertInputValue('.purchase__payment-hidden', 'card');
        });
    }
}
