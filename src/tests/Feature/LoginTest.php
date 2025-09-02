<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスを入力せずに会員登録を試行した場合のバリデーションテスト
     *
     * @return void
     */
    public function test_login_fails_when_email_is_empty()
    {
        // Userテーブルにデータを作成
        User::create([
            'name' => 'aaa',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 1. ログインページを開く
        $response = $this->get('/login');

        // 2. メールアドレスを入力せずに他の必要項目を入力する
        $registrationData = [
            'email' => '', // メールアドレスを空にする
            'password' => 'password123',
        ];

        // 3. ログインボタンを押す
        $response = $this->post('/login', $registrationData);

        // 「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrorsIn('default', [
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    /**
     * パスワードを入力せずに会員登録を試行した場合のバリデーションテスト
     *
     * @return void
     */
    public function test_login_fails_when_password_is_empty()
    {
        // Userテーブルにデータを作成
        User::create([
            'name' => 'aaa',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 1. ログインページを開く
        $response = $this->get('/login');

        // 2. パスワードを入力せずに他の必要項目を入力する
        $registrationData = [
            'email' => 'test@example.com',
            'password' => '', // パスワードを空にする
        ];

        // 3. ログインボタンを押す
        $response = $this->post('/login', $registrationData);

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrorsIn('default', [
            'password' => 'パスワードを入力してください'
        ]);
    }

    /**
     * 入力情報が間違ってる場合のバリデーションテスト
     *
     * @return void
     */
    public function test_login_fails_when_data_is_wrong()
    {
        // Userテーブルにデータを作成
        User::create([
            'name' => 'aaa',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 1. ログインページを開く
        $response = $this->get('/login');

        // 2. 必須項目にテーブルに存在しないデータを入力する
        $registrationData = [
            'email' => 'hoge@example.com',
            'password' => 'password',
        ];

        // 3. ログインボタンを押す
        $response = $this->post('/login', $registrationData);

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrorsIn('default', [
            'email' => 'ログイン情報が登録されていません'
        ]);
    }

    /**
     * 正常なログインのテスト
     *
     * @return void
     */
    public function test_successful_login()
    {
        // Userテーブルにデータを作成
        User::create([
            'name' => 'aaa',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 1. ログインページを開く
        $response = $this->get('/login');

        // 2. 必須項目にデータを入力する
        $registrationData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        // 3. ログインボタンを押す
        $response = $this->post('/login', $registrationData);

        // 成功時のリダイレクト（商品一覧画面）
        $response->assertRedirect('/');
    }
}
