# coachtech フリマ

## 環境構築

#### Docker ビルド

1. git clone git@github.com:robert1725s/flema-app.git
2. cd flema-app
3. docker-compose up -d

#### Laravel 環境構築

1. docker-compose exec php bash
2. composer install
3. cp .env.example .env
4. `.env` ファイルの 11 行目以降を以下に変更

```diff
// 前略

DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
+ DB_HOST=mysql
DB_PORT=3306
- DB_DATABASE=laravel
- DB_USERNAME=root
- DB_PASSWORD=
+ DB_DATABASE=laravel_db
+ DB_USERNAME=laravel_user
+ DB_PASSWORD=laravel_pass

// 後略
```

5. php artisan key:generate
6. php artisan storage:link
7. php artisan migrate
8. php artisan db:seed

## Stripe 決済の設定

#### Stripe アカウントの作成

1. [Stripe](https://stripe.com) にアクセス
2. アカウントを作成（無料）
3. ダッシュボードにログイン

#### テスト用 API キーの取得

1. Stripe ダッシュボードで「開発者」→「API キー」を選択
2. テスト環境のキーを確認：
    - **公開可能キー**: `pk_test_...` で始まる
    - **秘密キー**: `sk_test_...` で始まる

#### 環境変数の設定

`.env`ファイルの 40,41 行目に以下を設定：

```env
STRIPE_KEY=pk_test_your_public_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
```

#### コンビニ決済の有効化

1. Stripe ダッシュボードで「設定」→「Payments」を選択
2. 「決済手段」タブの「コンビニ決済」を有効にする

※これをしないとコンビニ決済を選んだ際、Stripe 決済画面に遷移できない

#### テスト用カード情報

Stripe 決済のテストには以下のカード番号を使用してください：

-   **カード番号**: `4242 4242 4242 4242`
-   **有効期限**: 任意の将来の日付（例: 12/34）
-   **CVC**: 任意の 3 桁の数字（例: 123）
-   **郵便番号**: 任意の 5 桁（例: 12345）

詳細は[Stripe のテストカード一覧](https://stripe.com/docs/testing#cards)を参照してください。

## 使用技術

-   **PHP 8.1**
-   **Laravel 8.8**
-   **MySQL 8.0**
-   **selenium 4.1.3**
-   **mailhog 1.0.1**

## ER 図

## URL

-   開発環境：http://localhost/
-   phpMyadmin：http://localhost:8080/
-   mailhog：http://localhost:8025/
