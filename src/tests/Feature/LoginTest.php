<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class LoginTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * メールアドレスを入力せずにログインを試行した場合のバリデーションテスト
     */
    public function test_login_fails_when_email_is_empty()
    {
        // ログインページの表示確認
        $response = $this->get('/login');
        $response->assertStatus(200);

        // メールアドレスを空にしてログインを実行
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrorsIn('default', ['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードを入力せずにログインを試行した場合のバリデーションテスト
     */
    public function test_login_fails_when_password_is_empty()
    {
        // ログインページの表示確認
        $response = $this->get('/login');
        $response->assertStatus(200);

        // パスワードを空にしてログインを実行
        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors(['password']);
        $response->assertSessionHasErrorsIn('default', ['password' => 'パスワードを入力してください']);
    }

    /**
     * 登録されていない情報でログインを試行した場合のバリデーションテスト
     */
    public function test_login_fails_when_data_is_wrong()
    {
        // ログインページの表示確認
        $response = $this->get('/login');
        $response->assertStatus(200);

        // 登録されていない情報でログインを実行
        $response = $this->from('/login')->post('/login', [
            'email' => 'notregistered@example.com',
            'password' => 'wrongpassword',
        ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrorsIn('default', ['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * 正しい情報が入力された場合、ログイン処理が実行されるテスト
     */
    public function test_successful_login()
    {
        // テストユーザーを作成
        $user = $this->createVerifiedUser();

        // ログインページの表示確認
        $response = $this->get('/login');
        $response->assertStatus(200);

        // 正しい情報でログインを実行
        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // ユーザーが認証されていることを確認
        $this->assertAuthenticatedAs($user);
    }
}
