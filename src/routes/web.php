<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('index');
});

// メール認証ページ
Route::get('/verify-code', function () {
    return view('auth.verify-notice');
});

Route::middleware('auth')->group(
    function () {
        // プロフィール編集ページ
        Route::get('/mypage/profile', function () {
            return view('profile');
        });
    }
);
