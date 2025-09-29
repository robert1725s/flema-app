<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;

class CommentTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * ログイン済みのユーザーはコメントを送信できる
     * 1. ユーザーにログインする
     * 2. コメントを入力する
     * 3. コメントボタンを押す
     */
    public function test_authenticated_user_can_post_comment()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->email_verified_at = now();
        $user->save();

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/test.jpg',
            'price' => 5000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) use ($item) {
            // 1. ユーザーにログインする
            $browser->visit('/login')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 商品詳細ページにアクセス
            $browser->visit("/item/{$item->id}")
                // 初期のコメント数を確認（0件）
                ->assertSeeIn('.detail__action-button--comment', '0')
                ->assertSee('コメント(0)')
                // 2. コメントを入力する
                ->type('[name="comment"]', 'これは新しいコメントです')
                // 3. コメントボタンを押す
                ->press('コメントを送信する')
                ->pause(500) // 処理待機
                // コメントが表示されることを確認
                ->assertSee('これは新しいコメントです')
                ->assertSee('テストユーザー')
                // コメント数が増加したことを確認（1件）
                ->assertSeeIn('.detail__action-button--comment', '1')
                ->assertSee('コメント(1)');
        });

        // データベースにコメントが登録されていることを確認
        $this->assertDatabaseHas('comments', [
            'content' => 'これは新しいコメントです',
            'item_id' => $item->id,
        ]);
    }

    /**
     * ログイン前のユーザーはコメントを送信できない
     * 1. コメントを入力する
     * 2. コメントボタンを押す
     */
    public function test_unauthenticated_user_cannot_post_comment()
    {
        // テストユーザーを作成
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/test.jpg',
            'price' => 5000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) use ($item) {
            // 未ログイン状態で商品詳細ページにアクセス
            $browser->visit("/item/{$item->id}")
                // 1. コメントを入力する
                ->type('[name="comment"]', 'ログインしていないユーザーのコメント')
                // 2. コメントボタンを押す
                ->press('コメントを送信する')
                ->pause(500)
                ->assertDontSee('ログインしていないユーザーのコメント');
        });

        // データベースにコメントが登録されていないことを確認
        $this->assertDatabaseMissing('comments', [
            'content' => 'ログインしていないユーザーのコメント',
        ]);
    }

    /**
     * コメントが入力されていない場合、バリデーションメッセージが表示される
     * 1. ユーザーにログインする
     * 2. コメントボタンを押す（コメント未入力）
     */
    public function test_empty_comment_shows_validation_message()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->email_verified_at = now();
        $user->save();

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/test.jpg',
            'price' => 5000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        $this->browse(function (Browser $browser) use ($item) {
            // 1. ユーザーにログインする
            $browser->visit('/login')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 商品詳細ページにアクセス
            $browser->visit("/item/{$item->id}")
                // 2. コメントボタンを押す（コメント未入力）
                ->press('コメントを送信する')
                ->pause(500)
                // バリデーションメッセージが表示されることを確認
                ->assertSee('コメントを入力してください');
        });

        // データベースにコメントが登録されていないことを確認
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * コメントが255字以上の場合、バリデーションメッセージが表示される
     * 1. ユーザーにログインする
     * 2. 255文字以上のコメントを入力する
     * 3. コメントボタンを押す
     */
    public function test_comment_over_255_characters_shows_validation_message()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->email_verified_at = now();
        $user->save();

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 商品を作成
        $item = Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品です',
            'image_path' => 'items/test.jpg',
            'price' => 5000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ]);

        // 256文字のコメント（255文字を超える）
        $longComment = str_repeat('あ', 256);

        $this->browse(function (Browser $browser) use ($item, $longComment) {
            // 1. ユーザーにログインする
            $browser->visit('/login')
                ->type('[name="email"]', 'test@example.com')
                ->type('[name="password"]', 'password123')
                ->press('ログインする')
                ->assertPathIs('/');

            // 商品詳細ページにアクセス
            $browser->visit("/item/{$item->id}")
                // 2. 255文字以上のコメントを入力する
                ->type('[name="comment"]', $longComment)
                // 3. コメントボタンを押す
                ->press('コメントを送信する')
                ->pause(500)
                // バリデーションメッセージが表示されることを確認
                ->assertSee('コメントは255文字以内で入力してください');
        });

        // データベースにコメントが登録されていないことを確認
        $this->assertDatabaseMissing('comments', [
            'content' => $longComment,
        ]);
    }
}
