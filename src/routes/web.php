<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StripeWebhookController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 商品一覧ページ
Route::get('/', [ItemController::class, 'index']);

// 商品詳細ページ
Route::get('/item/{item_id}', [ItemController::class, 'detail']);

// Stripe決済成功・キャンセル
Route::get('/purchase/success', [ItemController::class, 'stripeSuccess']);
Route::get('/purchase/cancel', [ItemController::class, 'stripeCancel']);

Route::middleware('auth')->group(
    function () {
        // メール認証誘導ページ
        Route::get('/notice', [UserController::class, 'notice']);

        // マイページ
        Route::get('/mypage', [UserController::class, 'mypage']);

        // プロフィール編集ページ
        Route::get('/mypage/profile', [UserController::class, 'profile']);
        // プロフィール更新処理
        Route::post('/mypage/profile', [UserController::class, 'update']);

        // 商品出品ページ
        Route::get('/sell', [ItemController::class, 'sell']);
        // 商品出品ページ 出品商品の保存処理
        Route::post('/sell', [ItemController::class, 'store']);

        // お気に入り機能
        Route::post('/item/favorite/{item_id}', [ItemController::class, 'favorite']);

        // コメント投稿機能
        Route::post('/item/comment/{item_id}', [ItemController::class, 'comment']);

        // 購入ページ
        Route::get('/purchase/{item_id}', [ItemController::class, 'purchase']);
        // 購入処理
        Route::post('/purchase/{item_id}', [ItemController::class, 'checkout']);

        // 住所変更ページ
        Route::get('/purchase/address/{item_id}', [ItemController::class, 'editAddress']);
        // 住所更新処理
        Route::post('/purchase/address/{item_id}', [ItemController::class, 'updateAddress']);
    }
);
