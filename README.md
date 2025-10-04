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
4. php artisan key:generate
5. php artisan storage:link
6. php artisan migrate
7. php artisan db:seed

##### テスト環境設定

1. cp .env.testing.example .env.testing
2. php artisan key:generate --env=testing

```
Seleniumを利用したBrowserTestを実行するには、以下のコマンドを実行する
(JavaScriptの動的な挙動も確認可能)
```

1. cp .env.dusk.example .env.dusk
2. php artisan key:generate --env=dusk

```
テスト実行コマンドは、下の#テスト実行を参照
```

## Stripe 決済の設定

#### Stripe アカウントの作成

1. [Stripe](https://stripe.com) にアクセス
2. アカウントを作成（無料）
3. ダッシュボードにログイン

#### テスト用 API キーの取得

Stripe ダッシュボードでテスト環境のキーを確認：

-   **公開可能キー**: `pk_test_...` で始まる
-   **秘密キー**: `sk_test_...` で始まる

#### 環境変数の設定

`.env`、`env.testing`、`.env.dusk`ファイルの 40,41 行目にキーを設定：

```env
STRIPE_KEY=pk_test_your_public_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
```

#### コンビニ決済の有効化

1. Stripe ダッシュボードで「設定」→「Payments」を選択
2. 「決済手段」タブの「コンビニ決済」を有効にする

※これをしないとコンビニ決済を選んだ際、Stripe 決済画面に遷移できない

#### テスト用カード情報

Stripe 決済には以下のカード番号を使用してください：

-   **カード番号**: `4242 4242 4242 4242`
-   **有効期限**: 任意の将来の日付（例: 12/34）
-   **CVC**: 任意の 3 桁の数字（例: 123）

詳細は[Stripe のテストカード一覧](https://stripe.com/docs/testing#cards)を参照してください。

#### ログイン情報

以下のユーザがシーディングファイルを実行することで、DB に登録されます。

```
・メール認証完了、住所情報ありユーザ
メールアドレス：admin@hoge.com
パスワード：12345678

・メール認証未完了、住所情報なしユーザ
メールアドレス：test@hoge.com
パスワード：12345678
```

#### テスト実行

UnitTest は、PHP コンテナ内で以下を実行

```
vendor/bin/phpunit
```

BrowserTest は、PHP コンテナ内で以下を実行

```php
// 全てのBrowserTestを実行
php artisan dusk
// 一部のBrowserTestを実行(例:RegisterTest.php)
php artisan dusk --filter RegisterTest
```

**注意事項**

Dusk テスト実行中に強制終了（Ctrl+C など）すると、`.env`ファイルが`.env.dusk`の内容で上書きされたままになる可能性があります。

この場合、以下の手順で復旧してください：

```bash
# .envファイルを元に戻す(.env.backupファイルが生成されてる場合)
cp .env.backup .env

# .envファイルを元に戻す
cp .env.example .env
php artisan key:generate
```

## 使用技術

-   **PHP 8.1**
-   **Laravel 8.8**
-   **MySQL 8.0**
-   **selenium 4.1.3**
-   **mailhog 1.0.1**
-   **JavaScript**

## ER 図

<img width="891" height="942" alt="Image" src="https://github.com/user-attachments/assets/aeb61e76-c686-464f-a9c9-30ba055fcfda" />

## URL

-   開発環境：http://localhost/
-   phpMyadmin：http://localhost:8080/
-   mailhog：http://localhost:8025/
