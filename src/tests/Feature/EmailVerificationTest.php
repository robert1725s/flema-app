<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class EmailVerificationTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 会員登録後、認証メールが送信される
     * 1. 会員登録をする
     * 2. 認証メールを送信する
     */
    public function test_verification_email_is_sent_after_registration()
    {
        // メール送信をフェイク
        Notification::fake();

        // 1. 会員登録をする
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 2. 登録したメールアドレス宛に認証メールが送信されている
        $user = \App\Models\User::where('email', 'test@example.com')->first();

        // 認証メールが送信されたことを確認
        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );
    }

    /**
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     * 1. メール認証導線画面を表示する
     * 2. 「認証はこちらから」ボタンを押下
     * 3. メール認証サイトを表示する
     */
    public function test_verification_button_redirects_to_mailhog()
    {
        // 未認証ユーザーを作成
        $user = $this->createUnverifiedUser();

        // 1. メール認証導線画面を表示する
        $this->actingAs($user);
        $response = $this->get('/notice');

        $response->assertStatus(200);

        // 「認証はこちらから」ボタンが表示されている
        $response->assertSee('認証はこちらから');

        // 2. 「認証はこちらから」ボタンを押下
        // Mailhogへのリンクが存在することを確認
        $mailhogUrl = config('services.mailhog.url');
        $response->assertSee($mailhogUrl, false);
    }

    /**
     * メール認証サイトのメール認証を完了すると、プロフィール設定画面に遷移する
     * 1. メール認証を完了する
     * 2. プロフィール設定画面を表示する
     */
    public function test_email_verification_redirects_to_profile_page()
    {
        // 未認証ユーザーを作成
        $user = $this->createUnverifiedUser();

        // メール認証URLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(10),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 1. メール認証を完了する
        $this->actingAs($user);
        $response = $this->get($verificationUrl);

        // 2. プロフィール設定画面を表示する（プロフィール設定画面に遷移する）
        $response->assertRedirect('/mypage/profile');

        // メール認証が完了していることを確認
        $this->assertNotNull($user->email_verified_at);
    }
}
