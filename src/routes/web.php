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
Route::get('/', [ItemController::class, 'showIndex']);

// 商品詳細ページ
Route::get('/item/{item_id}', [ItemController::class, 'showDetail']);

// Stripe決済成功・キャンセル
Route::get('/purchase/success', [ItemController::class, 'stripeSuccess']);
Route::get('/purchase/cancel', [ItemController::class, 'stripeCancel']);

Route::middleware('auth')->group(
    function () {
        // メール認証誘導ページ
        Route::get('/notice', [UserController::class, 'showNotice']);

        // お気に入り機能
        Route::post('/item/favorite/{item_id}', [ItemController::class, 'favoriteItem']);

        // コメント投稿機能
        Route::post('/item/comment/{item_id}', [ItemController::class, 'commentItem']);

        // 購入ページ
        Route::get('/purchase/{item_id}', [ItemController::class, 'showPurchase']);
        // 購入処理
        Route::post('/purchase/{item_id}', [ItemController::class, 'checkoutItem']);

        // 住所変更ページ
        Route::get('/purchase/address/{item_id}', [ItemController::class, 'showAddress']);
        // 住所更新処理
        Route::post('/purchase/address/{item_id}', [ItemController::class, 'updateAddress']);

        // 商品出品ページ
        Route::get('/sell', [ItemController::class, 'showSell']);
        // 商品出品ページ 出品商品の保存処理
        Route::post('/sell', [ItemController::class, 'storeItem']);

        // マイページ
        Route::get('/mypage', [UserController::class, 'showMypage']);

        // プロフィール編集ページ
        Route::get('/mypage/profile', [UserController::class, 'showProfile']);
        // プロフィール更新処理
        Route::post('/mypage/profile', [UserController::class, 'updateProfile']);
    }
);
