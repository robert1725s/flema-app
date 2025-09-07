<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;

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
        // プロフィール編集ページ
        Route::get('/mypage/profile', function () {
            return view('profile');
        });
        // 商品出品ページ
        Route::get('/sell', [ItemController::class, 'sell']);
        Route::post('/sell', [ItemController::class, 'store']);
    }
);
