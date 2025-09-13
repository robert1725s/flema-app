<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザー情報変更：変更項目が初期値として過去設定されていること
     * （プロフィール画像、ユーザー名、郵便番号、住所）
     * 1. ユーザーにログインする
     * 2. プロフィールページを開く
     *
     * @return void
     */
    public function test_profile_page_displays_existing_user_information_as_initial_values()
    {
        // テストユーザーを作成（すべての項目に値を設定）
        $user = User::create([
            'name' => '田中太郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password123'),
            'image_path' => 'user/tanaka-profile.jpg',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => 'テストマンション101号室',
        ]);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. プロフィールページを開く
        $response = $this->get('/mypage/profile');

        // プロフィール設定のタイトルが表示されている
        $response->assertSee('プロフィール設定');

        // プロフィール画像のパスが表示されている
        $response->assertSee('user/tanaka-profile.jpg');

        // ユーザー名の入力フィールドに初期値が設定されている
        $response->assertSee('value="田中太郎"', false);

        // 郵便番号の入力フィールドに初期値が設定されている
        $response->assertSee('value="123-4567"', false);

        // 住所の入力フィールドに初期値が設定されている
        $response->assertSee('value="東京都渋谷区道玄坂1-2-3"', false);

        // 建物名の入力フィールドに初期値が設定されている
        $response->assertSee('value="テストマンション101号室"', false);
    }
}
