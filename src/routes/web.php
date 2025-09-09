<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;

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

Route::middleware('auth')->group(
    function () {
        // メール認証誘導ページ
        Route::get('/notice', [UserController::class, 'notice']);

        // プロフィール編集ページ
        Route::get('/mypage/profile', [UserController::class, 'profile']);

        // 商品出品ページ
        Route::get('/sell', [ItemController::class, 'sell']);
        // 商品出品ページ 出品商品の保存処理
        Route::post('/sell', [ItemController::class, 'store']);
    }
);
