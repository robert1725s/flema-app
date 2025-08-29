@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/resister.css') }}" />
@endsection

@section('content')
    <div class="register__container">
        <h1 class="register__title">会員登録</h1>

        <form class="register__form" action="#" method="POST">
            @csrf

            <!-- ユーザー名 -->
            <div class="register__form-group">
                <label class="register__label" for="username">ユーザー名</label>
                <input name="username" class="register__input">
            </div>

            <!-- メールアドレス -->
            <div class="register__form-group">
                <label class="register__label" for="email">メールアドレス</label>
                <input name="email" class="register__input">
            </div>

            <!-- パスワード -->
            <div class="register__form-group">
                <label class="register__label" for="password">パスワード</label>
                <input name="password" class="register__input">
            </div>

            <!-- 確認用パスワード -->
            <div class="register__form-group">
                <label class="register__label" for="password_confirmation">確認用パスワード</label>
                <input name="password_confirmation" class="register__input">
            </div>

            <!-- 登録ボタン -->
            <button type="submit" class="register__submit">登録する</button>
        </form>

        <!-- ログインリンク -->
        <div class="register__login-link">
            <a href="#" class="register__link">ログインはこちら</a>
        </div>
    </div>
@endsection
