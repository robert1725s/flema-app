<?php

namespace Tests\Feature\Traits;

use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait TestHelpers
{
    /**
     * メール認証済みのユーザーを作成
     */
    protected function createVerifiedUser($attributes = [])
    {
        $defaults = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'postal_code' => '1234567',
            'address' => '東京都渋谷区テスト1-2-3',
            'building' => 'テストビル101',
        ];

        return User::create(array_merge($defaults, $attributes));
    }

    /**
     * メール未認証のユーザーを作成
     */
    protected function createUnverifiedUser($attributes = [])
    {
        $attributes['email_verified_at'] = null;
        return $this->createVerifiedUser($attributes);
    }

    /**
     * 出品者ユーザーを作成
     */
    protected function createSeller($attributes = [])
    {
        $defaults = [
            'name' => '出品者テスト',
            'email' => 'seller' . Str::random(5) . '@example.com',
        ];

        return $this->createVerifiedUser(array_merge($defaults, $attributes));
    }

    /**
     * 購入者ユーザーを作成
     */
    protected function createBuyer($attributes = [])
    {
        $defaults = [
            'name' => '購入者テスト',
            'email' => 'buyer' . Str::random(5) . '@example.com',
        ];

        return $this->createVerifiedUser(array_merge($defaults, $attributes));
    }

    /**
     * テスト用商品を作成
     */
    protected function createItem($seller, $attributes = [])
    {
        $defaults = [
            'name' => 'テスト商品_' . Str::random(5),
            'description' => 'これはテスト用の商品説明です',
            'brand' => 'テストブランド',
            'image_path' => 'items/test.jpg',
            'price' => 5000,
            'condition' => 1,
            'seller_id' => $seller->id,
        ];

        return Item::create(array_merge($defaults, $attributes));
    }

    /**
     * 売却済み商品を作成
     */
    protected function createSoldItem($seller, $buyer, $attributes = [])
    {
        $attributes['purchaser_id'] = $buyer->id;
        return $this->createItem($seller, $attributes);
    }

    /**
     * カテゴリを作成
     */
    protected function createCategory($content)
    {
        return Category::create(['content' => $content]);
    }

    /**
     * 複数のカテゴリを作成
     */
    protected function createCategories($contents = [])
    {
        if (empty($contents)) {
            $contents = ['ファッション', 'メンズ', 'トップス', '家電', 'スポーツ'];
        }

        return collect($contents)->map(function ($content) {
            return $this->createCategory($content);
        });
    }

    /**
     * コメントを作成
     */
    protected function createComment($user, $item, $content = null)
    {
        return Comment::create([
            'content' => $content ?: 'これはテストコメントです',
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * お気に入りを作成
     */
    protected function createFavorite($user, $item)
    {
        return Favorite::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * ログイン処理をシミュレート
     */
    protected function login($email = 'test@example.com', $password = 'password123')
    {
        return $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);
    }
}
