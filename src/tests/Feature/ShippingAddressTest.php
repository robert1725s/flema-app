<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class ShippingAddressTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
     * 1. ユーザーにログインする
     * 2. 送付先住所変更画面で住所を登録する
     * 3. 商品購入画面を再度開く
     */
    public function test_registered_address_is_reflected_on_purchase_screen()
    {
        // ユーザーと商品を作成
        $buyer = $this->createBuyer([
            'postal_code' => '1234567',
            'address' => '東京都渋谷区テスト1-2-3',
            'building' => 'テストビル101',
        ]);
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($buyer);

        // 初期状態の商品購入画面を開く
        $response = $this->get("/purchase/{$item->id}");
        $response->assertStatus(200);

        // デフォルトではユーザーの登録住所が表示される
        $response->assertSee('1234567');
        $response->assertSee('東京都渋谷区テスト1-2-3');
        $response->assertSee('テストビル101');

        // 2. 送付先住所変更画面で住所を登録する
        $response = $this->from("/purchase/address/{$item->id}")->post("/purchase/address/{$item->id}", [
            'postal_code' => '987-6543',
            'address' => '大阪府大阪市北区新住所4-5-6',
            'building' => '新ビル202',
        ]);

        // 3. 商品購入画面を再度開く
        $response = $this->get("/purchase/{$item->id}");

        // 登録した住所が商品購入画面に正しく反映される
        $response->assertSee('987-6543');
        $response->assertSee('大阪府大阪市北区新住所4-5-6');
        $response->assertSee('新ビル202');

        // 元の住所は表示されない
        $response->assertDontSee('東京都渋谷区テスト1-2-3');
        $response->assertDontSee('テストビル101');
    }

    /**
     * 購入した商品に送付先住所が紐づいて登録される
     * 1. ユーザーにログインする
     * 2. 送付先住所変更画面で住所を登録する
     * 3. 商品を購入する
     */
    public function test_shipping_address_is_linked_to_purchased_item()
    {
        // ユーザーと商品を作成
        $buyer = $this->createBuyer([
            'postal_code' => '1111111',
            'address' => '東京都新宿区初期住所1-1-1',
            'building' => '初期ビル101',
        ]);
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($buyer);

        // 2. 送付先住所変更画面で住所を登録する
        $response = $this->from("/purchase/address/{$item->id}")->post("/purchase/address/{$item->id}", [
            'postal_code' => '222-2222',
            'address' => '神奈川県横浜市変更後住所7-8-9',
            'building' => '変更後ビル303',
        ]);

        // 3. 商品を購入する
        $response = $this->post("/purchase/{$item->id}", [
            'payment_method' => 'card',
        ]);

        // responseのリダイレクト先を確認して、/purchase/success?session_id=が含まれていることを確認(購入成功ルート)
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/purchase/success?session_id', $redirectUrl);

        // リダイレクトを実行し、購入処理を完了させる
        $response = $this->get($redirectUrl);

        // 正しく送付先住所が紐づいている
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'purchaser_id' => $buyer->id,
            'postal_code' => '222-2222',
            'address' => '神奈川県横浜市変更後住所7-8-9',
            'building' => '変更後ビル303',
        ]);
    }
}
