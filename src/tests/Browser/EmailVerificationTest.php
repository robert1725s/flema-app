<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class EmailVerificationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 会員登録後、認証メールが送信される
     * 1. 会員登録をする
     * 2. 認証メールを送信される
     */
    public function test_verification_email_sent_after_registration()
    {
        $this->browse(function (Browser $browser) {
            // 1. 会員登録をする
            $browser->visit('/register')
                ->pause(1000)
                ->assertSee('会員登録')
                ->type('[name="name"]', 'テストユーザー')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->type('[name="password_confirmation"]', 'password123')
                ->pause(500)
                ->press('登録する')
                ->pause(2000);

            // 登録後のページを確認（メール認証誘導画面に遷移）
            $browser->assertPathIs('/notice')
                ->assertSee('登録していただいたメールアドレスに認証メールを送付しました')
                ->pause(1000);

            // 2. 認証メールが送信される
            $browser->visit('http://mailhog:8025/')
                ->pause(3000);

            // 最新メールをクリックして開く
            $browser->click('.messages .msglist-message:first-child')
                ->pause(2000);

            // ユーザーのemailと"Verify Email Address"という文字があることを確認
            $browser->assertSee('test@example.com')
                ->assertSee('Verify Email Address');
        });
    }

    /**
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     * 1. メール認証導線画面を表示する
     * 2. 「認証はこちらから」ボタンを押下
     * 3. メール認証サイトを表示する
     * http://localhost:8025/に遷移することを確認
     */
    public function test_verification_notice_page_has_mailhog_link()
    {
        $this->browse(function (Browser $browser) {
            // 1. メール認証導線画面を表示する
            $browser->visit('/register')
                ->pause(1000)
                ->assertSee('会員登録')
                ->type('[name="name"]', 'テストユーザー')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->type('[name="password_confirmation"]', 'password123')
                ->pause(500)
                ->press('登録する')
                ->pause(2000);

            // 2. 「認証はこちらから」ボタンを押下
            $browser->click('.notice__button')
                ->pause(3000)
                // 3. メール認証サイトが表示されたことを確認
                ->assertUrlIs('http://mailhog:8025/');
        });
    }

    /**
     * メール認証サイトのメール認証を完了すると、プロフィール設定ページに遷移する
     * 1. メール認証を完了する
     * http://localhost:8025/にアクセスし、ユーザーの名前をクリックする
     * 遷移先でVerify Email Addressをクリックし、プロフィール設定画面に遷移することを確認する
     * 2. プロフィール設定画面を表示する
     */
    public function test_email_verification_redirects_to_profile_page()
    {
        $this->browse(function (Browser $browser) {
            // 1. メール認証導線画面を表示する
            $browser->visit('/register')
                ->pause(1000)
                ->assertSee('会員登録')
                ->type('[name="name"]', 'テストユーザー')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->type('[name="password_confirmation"]', 'password123')
                ->pause(500)
                ->press('登録する')
                ->pause(2000);

            // 2. 「認証はこちらから」ボタンを押下
            $browser->click('.notice__button')
                ->pause(3000)
                // 3. メール認証サイトが表示されたことを確認
                ->assertUrlIs('http://mailhog:8025/');

            // 最新のメールをクリック
            $browser->click('.messages .msglist-message:first-child')
                ->pause(2000);

            // Verify Email Addressリンクを取得してクリック
            $browser->withinFrame('iframe', function ($browser) {
                // 遷移先でVerify Email Addressをクリック
                $browser->assertSee('Verify Email Address')
                    ->pause(1000)
                    ->clickLink('Verify Email Address')
                    ->pause(3000);
            });

            // 新しいタブに切り替え
            $windows = $browser->driver->getWindowHandles();

            $browser->driver->switchTo()->window($windows[count($windows) - 1]);
            $browser->pause(2000)
                // プロフィール設定画面に遷移
                ->assertPathIs('/mypage/profile')
                ->assertSee('プロフィール設定')
                ->pause(1000);
        });
    }
}
