<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class PurchaseTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 「購入する」ボタンを押下すると購入が完了する
     * 1. ユーザーにログインする
     * 2. 商品購入画面を開く
     * 3. 商品を選択して「購入する」ボタンを押下
     */
    public function test_purchase_completes_when_button_clicked()
    {
        // ユーザーと商品を作成
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($buyer);

        // 2. 商品購入画面を開く
        $response = $this->get("/purchase/{$item->id}");
        $response->assertSee($item->name);

        // 3. 商品を選択して「購入する」ボタンを押下
        $response = $this->from("/purchase/{$item->id}")->post("/purchase/{$item->id}", [
            'payment_method' => 'card',
        ]);

        // responseのリダイレクト先を確認して、/purchase/success?session_id=が含まれていることを確認(購入成功ルート)
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/purchase/success?session_id', $redirectUrl);

        // リダイレクトを実行し、購入処理を完了させる
        $response = $this->get($redirectUrl);

        // 購入が完了していることを確認
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
    public function test_purchased_item_shows_sold_on_list()
    {
        // ユーザーと商品を作成
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($buyer);

        // 2. 商品購入画面を開く
        $response = $this->get("/purchase/{$item->id}");
        $response->assertSee($item->name);

        // 3. 商品を選択して「購入する」ボタンを押下
        $response = $this->from("/purchase/{$item->id}")->post("/purchase/{$item->id}", [
            'payment_method' => 'card',
        ]);

        // responseのリダイレクト先を確認して、/purchase/success?session_id=が含まれていることを確認(購入成功ルート)
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/purchase/success?session_id', $redirectUrl);

        // リダイレクトを実行し、購入処理を完了させる
        $response = $this->get($redirectUrl);

        // 4. 商品一覧画面を表示する
        $response = $this->get('/');

        // 購入した商品が「SOLD」と表示されることを確認
        $response->assertSee($item->name);
        $response->assertSee('SOLD');

        // データベースでも購入完了を確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'purchaser_id' => $buyer->id,
        ]);
    }

    /**
     * 「プロフィール/購入した商品一覧」に追加されている
     * 1. ユーザーにログインする
     * 2. 商品購入画面を開く
     * 3. 商品を選択して「購入する」ボタンを押下
     * 4. プロフィール画面を表示する
     */
    public function test_purchased_item_added_to_profile()
    {
        // ユーザーと商品を作成
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($buyer);

        // 2. 商品購入画面を開く
        $response = $this->get("/purchase/{$item->id}");
        $response->assertSee($item->name);

        // 3. 商品を選択して「購入する」ボタンを押下
        $response = $this->from("/purchase/{$item->id}")->post("/purchase/{$item->id}", [
            'payment_method' => 'card',
        ]);

        // responseのリダイレクト先を確認して、/purchase/success?session_id=が含まれていることを確認(購入成功ルート)
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/purchase/success?session_id', $redirectUrl);

        // リダイレクトを実行し、購入処理を完了させる
        $response = $this->get($redirectUrl);

        // 4. プロフィール画面を表示する
        $response = $this->get('/mypage?page=buy');

        // 購入した商品が一覧に表示されることを確認
        $response->assertSee($item->name);

        // データベースでも購入完了を確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'purchaser_id' => $buyer->id,
        ]);
    }
}
