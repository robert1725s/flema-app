<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後、認証メールが送信される
     */
    public function test_verification_email_sent_after_registration()
    {
        \Illuminate\Support\Facades\Notification::fake();

        // 1. 会員登録をする
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $user = User::where('email', 'test@example.com')->first();

        // 2. 認証メールを送信する（認証メール通知が送信されていることを確認）
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    /**
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function test_verification_notice_page_has_mailhog_link()
    {
        // 未認証のユーザーを作成
        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // ログイン
        $this->actingAs($user);

        // 1. メール認証導線画面を表示する
        $response = $this->get('/notice');
        $response->assertOk();

        // 2. 「認証はこちらから」ボタンを押下
        $response->assertSee('認証はこちらから');

        // 3. 認証サイトを表示する
        $mailhogResponse = $this->get('http://localhost:8025/');
        $mailhogResponse->assertOk();
    }

    /**
     * メール認証サイトのメール認証を完了すると、プロフィール編集ページに遷移する
     */
    public function test_email_verification_redirects_to_profile_page()
    {
        // 未認証のユーザーを作成
        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // ログイン
        $this->actingAs($user);

        // 1. メール認証を完了する（メール認証用のURLを生成してアクセス）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($verificationUrl);

        // 2. プロフィール設定画面を表示する
        $response->assertRedirect('/mypage/profile');
    }
}
