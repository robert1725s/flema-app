<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * メールアドレスを入力せずにログインを試行した場合のバリデーションテスト
     */
    public function test_login_fails_when_email_is_empty()
    {
        // Userテーブルにデータを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->clear('[name="email"]')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/login')
                ->assertSee('メールアドレスを入力してください');
        });
    }

    /**
     * パスワードを入力せずにログインを試行した場合のバリデーションテスト
     */
    public function test_login_fails_when_password_is_empty()
    {
        // Userテーブルにデータを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('[name="email"]', 'test@example.com')
                ->clear('[name="password"]')
                ->press('ログインする')
                ->assertPathIs('/login')
                ->assertSee('パスワードを入力してください');
        });
    }

    /**
     * 入力情報が間違っている場合のバリデーションテスト
     */
    public function test_login_fails_when_data_is_wrong()
    {
        // Userテーブルにデータを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('[name="email"]', 'wrong@example.com')
                ->type('[name="password"]', 'wrongpassword')
                ->press('ログインする')
                ->assertPathIs('/login')
                ->assertSee('ログイン情報が登録されていません');
        });
    }

    /**
     * 正常なログインのテスト
     */
    public function test_successful_login()
    {
        // Userテーブルにデータを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $user->email_verified_at = now();
        $user->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/')
                ->assertSee('ログアウト');
        });
    }
}
