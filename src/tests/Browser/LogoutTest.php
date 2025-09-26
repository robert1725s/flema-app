<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LogoutTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 正常なログアウトのテスト
     */
    public function test_successful_logout()
    {
        // Userテーブルにデータを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->email_verified_at = now();
        $user->save();

        $this->browse(function (Browser $browser) {
            // 1. ログインする
            $browser->visit('/login')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 2. ログアウトボタンを押す
            $browser->press('ログアウト')
                ->assertPathIs('/login');

            // 3. ログアウト後のアクセス制限確認(ログアウト処理ができているか)
            $browser->visit('/mypage')
                ->assertPathIs('/login');
        });
    }
}
