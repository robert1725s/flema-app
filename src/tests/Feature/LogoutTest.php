<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class LogoutTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * ログアウトができる
     * 1. ユーザーにログインをする
     * 2. ログアウトボタンを押す
     */
    public function test_successful_logout()
    {
        // テストユーザーを作成
        $user = $this->createVerifiedUser();

        // 1. ユーザーにログインをする
        $this->actingAs($user);

        // ユーザーが認証されていることを確認
        $this->assertAuthenticatedAs($user);

        // 2. ログアウトボタンを押す
        $response = $this->post('/logout');

        // ログアウト処理が実行される（ログインページにリダイレクト）
        $response->assertRedirect('/login');

        // ユーザーが認証されていないことを確認
        $this->assertGuest();
    }
}
