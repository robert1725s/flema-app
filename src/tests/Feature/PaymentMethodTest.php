<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class PaymentMethodTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 小計画面で変更が反映される
     * 1. 支払い方法選択画面を開く
     * 2. プルダウンメニューから支払い方法を選択する
     */
    public function test_payment_method_is_reflected_in_summary()
    {
        // ユーザーと商品を作成
        $buyer = $this->createBuyer([
            'email' => 'buyer@example.com',
        ]);
        $seller = $this->createSeller([
            'email' => 'seller@example.com',
        ]);
        $item = $this->createItem($seller, [
            'name' => 'テスト支払い商品',
            'price' => 5000,
        ]);

        // ユーザーにログイン
        $this->actingAs($buyer);

        // 1. 支払い方法選択画面を開く（商品購入画面）
        $response = $this->get("/purchase/{$item->id}");

        $response->assertStatus(200);

        // 初期状態では「選択してください」が表示される
        $response->assertSee('選択してください');

        // 2. プルダウンメニューから支払い方法を選択する（コンビニ払い）
        $response = $this->from("/purchase/{$item->id}")->withSession([
            '_old_input' => [
                'payment_method' => 'konbini',
            ],
        ])->get("/purchase/{$item->id}");

        // 選択した支払い方法が正しく反映される（selectでkonbiniが選択状態）
        $response->assertSee('value="konbini" selected', false);

        // 2. プルダウンメニューから支払い方法を選択する（カード支払い）
        $response = $this->from("/purchase/{$item->id}")->withSession([
            '_old_input' => [
                'payment_method' => 'card',
            ],
        ])->get("/purchase/{$item->id}");

        // 選択した支払い方法が正しく反映される（selectでcardが選択状態）
        $response->assertSee('value="card" selected', false);
    }
}
