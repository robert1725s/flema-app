<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前を入力せずに会員登録を試行した場合のバリデーションテスト
     *
     * @return void
     */
    public function test_registration_fails_when_name_is_empty()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');

        // 2. 名前を入力せずに他の必要項目を入力する
        $registrationData = [
            'name' => '', // 名前を空にする
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 3. 登録ボタンを押す
        $response = $this->post('/register', $registrationData);

        // 「お名前を入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrorsIn('default', [
            'name' => 'お名前を入力してください'
        ]);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com'
        ]);
    }

    /**
     * メールアドレスを入力せずに会員登録を試行した場合のバリデーションテスト
     *
     * @return void
     */
    public function test_registration_fails_when_email_is_empty()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');

        // 2. メールアドレスを入力せずに他の必要項目を入力する
        $registrationData = [
            'name' => 'テストユーザー',
            'email' => '', // メールアドレスを空にする
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 3. 登録ボタンを押す
        $response = $this->post('/register', $registrationData);

        // 「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrorsIn('default', [
            'email' => 'メールアドレスを入力してください'
        ]);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー'
        ]);
    }

    /**
     * パスワードを入力せずに会員登録を試行した場合のバリデーションテスト
     *
     * @return void
     */
    public function test_registration_fails_when_password_is_empty()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');

        // 2. パスワードを入力せずに他の必要項目を入力する
        $registrationData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '', // パスワードを空にする
            'password_confirmation' => '',
        ];

        // 3. 登録ボタンを押す
        $response = $this->post('/register', $registrationData);

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrorsIn('default', [
            'password' => 'パスワードを入力してください'
        ]);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー'
        ]);
    }

    /**
     * 7文字以下のパスワードを入力して会員登録を試行した場合のバリデーションテスト
     *
     * @return void
     */
    public function test_registration_fails_when_password_is_less_letter()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');

        // 2. 確認用パスワードを入力せずに他の必要項目を入力する
        $registrationData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass', // パスワードを7文字以下にする
            'password_confirmation' => '',
        ];

        // 3. 登録ボタンを押す
        $response = $this->post('/register', $registrationData);

        // 「パスワードは8文字以上で入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrorsIn('default', [
            'password' => 'パスワードは8文字以上で入力してください'
        ]);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー'
        ]);
    }

    /**
     * 確認用パスワードを入力せずに会員登録を試行した場合のバリデーションテスト
     *
     * @return void
     */
    public function test_registration_fails_when_password_confirmation_is_empty()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');

        // 2. 確認用パスワードを入力せずに他の必要項目を入力する
        $registrationData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => '', // 確認用パスワードを空にする
        ];

        // 3. 登録ボタンを押す
        $response = $this->post('/register', $registrationData);

        // 「パスワードと一致しません」というバリデーションメッセージが表示される
        $response->assertSessionHasErrorsIn('default', [
            'password_confirmation' => 'パスワードと一致しません'
        ]);

        // データベースにユーザーが作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー'
        ]);
    }

    /**
     * 正常な会員登録のテスト
     *
     * @return void
     */
    public function test_successful_registration()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');

        // 2. 必要項目を入力する
        $registrationData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 3. 登録ボタンを押す
        $response = $this->post('/register', $registrationData);

        // 成功時のリダイレクト（プロフィール編集画面）
        $response->assertRedirect('/mypage/profile');

        // データベースにユーザーが作成されたことを確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com'
        ]);
    }
}
