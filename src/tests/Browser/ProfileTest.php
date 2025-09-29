<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

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
        $user->email_verified_at = now();
        $user->save();

        $this->browse(function (Browser $browser) use ($user) {
            // 1. ユーザーにログインする
            $browser->loginAs($user)
                // 2. プロフィールページを開く
                ->visit('/mypage/profile')
                // プロフィール設定のタイトルが表示されている
                ->assertSee('プロフィール設定')
                // プロフィール画像が表示されている
                ->assertPresent('.profile__avatar-img')
                // ユーザー名の入力フィールドに初期値が設定されている
                ->assertInputValue('[name="name"]', '田中太郎')
                // 郵便番号の入力フィールドに初期値が設定されている
                ->assertInputValue('[name="postal_code"]', '123-4567')
                // 住所の入力フィールドに初期値が設定されている
                ->assertInputValue('[name="address"]', '東京都渋谷区道玄坂1-2-3')
                // 建物名の入力フィールドに初期値が設定されている
                ->assertInputValue('[name="building"]', 'テストマンション101号室');
        });
    }
}
