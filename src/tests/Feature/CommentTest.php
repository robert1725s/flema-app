<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Feature\Traits\TestHelpers;

class CommentTest extends TestCase
{
    use DatabaseMigrations;
    use TestHelpers;

    /**
     * ログイン済みのユーザーはコメントを送信できる
     * 1. ユーザーにログインする
     * 2. コメントを入力する
     * 3. コメントボタンを押す
     */
    public function test_authenticated_user_can_submit_comment()
    {
        // ユーザーを作成
        $user = $this->createVerifiedUser();

        // 出品者と商品を作成
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");

        // 初期状態のコメント数を確認（0件）
        $html = $response->getContent();
        preg_match('/<button[^>]*detail__action-button--comment[^>]*>.*?<span[^>]*detail__action-count[^>]*>(\d+)<\/span>/s', $html, $matches);
        $this->assertEquals('0', $matches[1] ?? '0');

        // 2. コメントを入力する
        // 3. コメントボタンを押す
        $response = $this->from("/item/{$item->id}")->post("/item/comment/{$item->id}", [
            'comment' => 'これはテストコメントです。',
        ]);

        // コメントが保存されることを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'これはテストコメントです。',
        ]);

        // リダイレクト後のページを確認
        $response = $this->get("/item/{$item->id}");
        $html = $response->getContent();
        preg_match('/<button[^>]*detail__action-button--comment[^>]*>.*?<span[^>]*detail__action-count[^>]*>(\d+)<\/span>/s', $html, $matches);
        $this->assertEquals('1', $matches[1] ?? '0');

        // コメント内容が表示されている
        $response->assertSee('これはテストコメントです。');
    }

    /**
     * ログイン前のユーザーはコメントを送信できない
     * 1. コメントを入力する
     * 2. コメントボタンを押す
     */
    public function test_unauthenticated_user_cannot_submit_comment()
    {
        // 出品者と商品を作成
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. コメントを入力する
        // 2. コメントボタンを押す（未認証）
        $response = $this->from("/item/{$item->id}")->post("/item/comment/{$item->id}", [
            'comment' => 'これはテストコメントです。',
        ]);

        // コメントが送信されず、ログインページにリダイレクトされる
        $response->assertRedirect('/login');

        // コメントがデータベースに保存されていない
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'content' => 'これはテストコメントです。',
        ]);
    }

    /**
     * コメントが入力されていない場合、バリデーションメッセージが表示される
     * 1. ユーザーにログインする
     * 2. コメントボタンを押す
     */
    public function test_validation_error_when_comment_is_empty()
    {
        // ユーザーを作成
        $user = $this->createVerifiedUser();

        // 出品者と商品を作成
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. コメントボタンを押す（コメント未入力）
        $response = $this->from("/item/{$item->id}")->post("/item/comment/{$item->id}", [
            'comment' => '',
        ]);

        // バリデーションエラーが発生し、元のページにリダイレクトされる
        $response->assertRedirect("/item/{$item->id}");

        // バリデーションメッセージの内容を確認
        $response->assertSessionHasErrorsIn('default', ['comment' => 'コメントを入力してください']);

        // コメントがデータベースに保存されていない
        $this->assertDatabaseMissing('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * コメントが255字以上の場合、バリデーションメッセージが表示される
     * 1. ユーザーにログインする
     * 2. 255文字以上のコメントを入力する
     * 3. コメントボタンを押す
     */
    public function test_validation_error_when_comment_exceeds_255_characters()
    {
        // ユーザーを作成
        $user = $this->createVerifiedUser();

        // 出品者と商品を作成
        $seller = $this->createSeller();
        $item = $this->createItem($seller);

        // 1. ユーザーにログインする
        $this->actingAs($user);

        // 2. 255文字以上のコメントを入力する
        $longComment = str_repeat('あ', 256);

        // 3. コメントボタンを押す
        $response = $this->from("/item/{$item->id}")->post("/item/comment/{$item->id}", [
            'comment' => $longComment,
        ]);

        // バリデーションエラーが発生し、元のページにリダイレクトされる
        $response->assertRedirect("/item/{$item->id}");

        // バリデーションメッセージの内容を確認
        $response->assertSessionHasErrorsIn('default', ['comment' => 'コメントは255文字以内で入力してください']);

        // コメントがデータベースに保存されていない
        $this->assertDatabaseMissing('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => $longComment,
        ]);
    }
}
