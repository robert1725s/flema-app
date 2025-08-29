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

// 会員登録ページ
Route::get('/register', function () {
    return view('auth.register');
});

// ログインページ
Route::get('/login', function () {
    return view('auth.login');
});

// メール認証ページ
Route::get('/verify-code', function () {
    return view('auth.verify-notice');
});

// プロフィール編集ページ
Route::get('/mypage/profile', function () {
    return view('profile');
});
