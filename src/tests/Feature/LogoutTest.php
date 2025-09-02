<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常なログアウトのテスト
     *
     * @return void
     */
    public function test_successful_logout()
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

        // ログイン後に認証されていることを確認
        $this->assertAuthenticated();

        // 4. ログアウトボタンを押す
        $response = $this->post('/logout', $registrationData);

        // ログアウト後に認証されていないことを確認
        $this->assertGuest();
    }
}
