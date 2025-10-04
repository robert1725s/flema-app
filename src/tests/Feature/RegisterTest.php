<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * 名前を入力せずに会員登録を試行した場合のバリデーションテスト
     */
    public function test_registration_fails_when_name_is_empty()
    {
        // 会員登録ページの表示確認
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 名前を空にして会員登録を実行
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors(['name']);
        $response->assertSessionHasErrorsIn('default', ['name' => 'お名前を入力してください']);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com'
        ]);
    }

    /**
     * メールアドレスを入力せずに会員登録を試行した場合のバリデーションテスト
     */
    public function test_registration_fails_when_email_is_empty()
    {
        // 会員登録ページの表示確認
        $response = $this->get('/register');
        $response->assertStatus(200);

        // メールアドレスを空にして会員登録を実行
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrorsIn('default', ['email' => 'メールアドレスを入力してください']);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー'
        ]);
    }

    /**
     * パスワードを入力せずに会員登録を試行した場合のバリデーションテスト
     */
    public function test_registration_fails_when_password_is_empty()
    {
        // 会員登録ページの表示確認
        $response = $this->get('/register');
        $response->assertStatus(200);

        // パスワードを空にして会員登録を実行
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => ''
        ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors(['password']);
        $response->assertSessionHasErrorsIn('default', ['password' => 'パスワードを入力してください']);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー'
        ]);
    }

    /**
     * 7文字以下のパスワードを入力して会員登録を試行した場合のバリデーションテスト
     */
    public function test_registration_fails_when_password_is_less_letter()
    {
        // 会員登録ページの表示確認
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 7文字以下のパスワードで会員登録を実行
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass'
        ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors(['password']);
        $response->assertSessionHasErrorsIn('default', ['password' => 'パスワードは8文字以上で入力してください']);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー'
        ]);
    }

    /**
     * パスワードと確認用パスワードが異なる場合のバリデーションテスト
     */
    public function test_registration_fails_when_passwords_do_not_match()
    {
        // 会員登録ページの表示確認
        $response = $this->get('/register');
        $response->assertStatus(200);

        // パスワードと確認用パスワードが異なる状態で会員登録を実行
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword'
        ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors(['password_confirmation']);
        $response->assertSessionHasErrorsIn('default', ['password_confirmation' => 'パスワードと一致しません']);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー'
        ]);
    }

    /**
     * 正常な会員登録のテスト
     */
    public function test_successful_registration()
    {
        // 会員登録ページの表示確認
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 正常な会員登録を実行
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // 登録後のリダイレクトを確認（メール認証誘導画面に遷移）
        $response->assertRedirect('/notice');

        // データベースにユーザーが作成されたことを確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com'
        ]);
    }
}
