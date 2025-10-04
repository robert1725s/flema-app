<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class MypageTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
     * 1. ユーザーにログインする
     * 2. プロフィールページを開く
     */
    public function test_mypage_displays_required_information()
    {
        // ユーザーを作成
        $user = $this->createVerifiedUser([
            'image_path' => 'profiles/test-profile.jpg',
        ]);

        // 別のユーザー（取引相手）を作成
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();

        // ユーザーが出品した商品を作成
        $soldItem1 = $this->createItem($user, [
            'name' => 'ユーザー出品商品1'
        ]);
        $soldItem2 = $this->createItem($user, [
            'name' => 'ユーザー出品商品2'
        ]);

        // ユーザーが購入した商品を作成
        $purchasedItem1 = $this->createSoldItem($seller, $user, [
            'name' => 'ユーザー購入商品1'
        ]);
        $purchasedItem2 = $this->createSoldItem($seller, $user, [
            'name' => 'ユーザー購入商品2'
        ]);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. プロフィールページを開く
        $response = $this->get('/mypage');

        // プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧が正しく表示される

        // ユーザー名の表示確認
        $response->assertSee('テストユーザー');

        // プロフィール画像のパス確認
        $response->assertSee('storage/profiles/test-profile.jpg', false);

        // デフォルトでは「出品した商品」タブが表示される
        // 出品した商品一覧の表示確認
        $response->assertSee('ユーザー出品商品1');
        $response->assertSee('ユーザー出品商品2');

        // 購入した商品タブを開く
        $response = $this->get('/mypage?page=buy');

        // 購入した商品一覧の表示確認
        $response->assertSee('ユーザー購入商品1');
        $response->assertSee('ユーザー購入商品2');
    }
}
