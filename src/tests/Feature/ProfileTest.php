<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class ProfileTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * 変更項目が初期値として過去設定されていること（プロフィール画像、ユーザー名、郵便番号、住所）
     * 1. ユーザーにログインする
     * 2. プロフィールページを開く
     */
    public function test_profile_page_displays_initial_values()
    {
        // ユーザーを作成（各項目に初期値を設定）
        $user = $this->createVerifiedUser([
            'name' => 'プロフィールテストユーザー',
            'email' => 'profile@example.com',
            'image_path' => 'profiles/initial-profile.jpg',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区テスト1-2-3',
            'building' => 'テストビル101',
        ]);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. プロフィールページを開く
        $response = $this->get('/mypage/profile');

        // 各項目の初期値が正しく表示されている

        // プロフィール画像のパス確認
        $response->assertSee('storage/profiles/initial-profile.jpg', false);

        // ユーザー名の初期値確認
        $response->assertSee('value="プロフィールテストユーザー"', false);

        // 郵便番号の初期値確認
        $response->assertSee('value="123-4567"', false);

        // 住所の初期値確認
        $response->assertSee('value="東京都渋谷区テスト1-2-3"', false);

        // 建物名の初期値確認
        $response->assertSee('value="テストビル101"', false);
    }
}
