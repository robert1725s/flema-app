<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RegisterTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 名前を入力せずに会員登録を試行した場合のバリデーションテスト
     */
    public function test_registration_fails_when_name_is_empty()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->clear('[name="name"]')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->type('[name="password_confirmation"]', 'password123')
                ->press('登録する')
                ->assertSee('お名前を入力してください');

            // データベースにユーザーが作成されていないことを確認
            $this->assertDatabaseMissing('users', [
                'email' => 'test@example.com'
            ]);
        });
    }

    /**
     * メールアドレスを入力せずに会員登録を試行した場合のバリデーションテスト
     */
    public function test_registration_fails_when_email_is_empty()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('[name="name"]', 'テストユーザー')
                ->clear('[name="email"]')
                ->type('[name="password"]', 'password123')
                ->type('[name="password_confirmation"]', 'password123')
                ->press('登録する')
                ->assertSee('メールアドレスを入力してください');

            // データベースにユーザーが作成されていないことを確認
            $this->assertDatabaseMissing('users', [
                'name' => 'テストユーザー'
            ]);
        });
    }

    /**
     * パスワードを入力せずに会員登録を試行した場合のバリデーションテスト
     */
    public function test_registration_fails_when_password_is_empty()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('[name="name"]', 'テストユーザー')
                ->type('[name="email"]', 'test@example.com')
                ->clear('[name="password"]')
                ->clear('[name="password_confirmation"]')
                ->press('登録する')
                ->assertSee('パスワードを入力してください');

            // データベースにユーザーが作成されていないことを確認
            $this->assertDatabaseMissing('users', [
                'name' => 'テストユーザー'
            ]);
        });
    }

    /**
     * 7文字以下のパスワードを入力して会員登録を試行した場合のバリデーションテスト
     */
    public function test_registration_fails_when_password_is_less_letter()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('[name="name"]', 'テストユーザー')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'pass')
                ->type('[name="password_confirmation"]', 'pass')
                ->press('登録する')
                ->assertSee('パスワードは8文字以上で入力してください');

            // データベースにユーザーが作成されていないことを確認
            $this->assertDatabaseMissing('users', [
                'name' => 'テストユーザー'
            ]);
        });
    }

    /**
     * パスワードと確認用パスワードが異なる場合のバリデーションテスト
     */
    public function test_registration_fails_when_passwords_do_not_match()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('[name="name"]', 'テストユーザー')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->type('[name="password_confirmation"]', 'differentpassword')
                ->press('登録する')
                ->assertSee('パスワードと一致しません');

            // データベースにユーザーが作成されていないことを確認
            $this->assertDatabaseMissing('users', [
                'name' => 'テストユーザー'
            ]);
        });
    }

    /**
     * 正常な会員登録のテスト
     */
    public function test_successful_registration()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('[name="name"]', 'テストユーザー')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->type('[name="password_confirmation"]', 'password123')
                ->press('登録する')
                ->assertPathIs('/notice');

            // データベースにユーザーが作成されたことを確認
            $this->assertDatabaseHas('users', [
                'name' => 'テストユーザー',
                'email' => 'test@example.com'
            ]);
        });
    }
}
