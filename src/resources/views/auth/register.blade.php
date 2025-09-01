@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/resister.css') }}" />
@endsection

@section('content')
    <div class="register__container">
        <h1 class="register__title">会員登録</h1>

        <form class="register__form" action="/register" method="POST">
            @csrf

            <!-- ユーザー名 -->
            <div class="register__form-group">
                <label class="register__label" for="username">ユーザー名</label>
                <input name="name" class="register__input" value="{{ old('name') }}">
                @error('name')
                    <div class="register__error">{{ $message }}</div>
                @enderror
            </div>

            <!-- メールアドレス -->
            <div class="register__form-group">
                <label class="register__label" for="email">メールアドレス</label>
                <input name="email" class="register__input" value="{{ old('email') }}">
                @error('email')
                    <div class="register__error">{{ $message }}</div>
                @enderror
            </div>

            <!-- パスワード -->
            <div class="register__form-group">
                <label class="register__label" for="password">パスワード</label>
                <input name="password" type="password" class="register__input">
                @error('password')
                    <div class="register__error">{{ $message }}</div>
                @enderror
            </div>

            <!-- 確認用パスワード -->
            <div class="register__form-group">
                <label class="register__label" for="password_confirmation">確認用パスワード</label>
                <input name="password_confirmation" type="password" class="register__input">
                @error('password_confirmation')
                    <div class="register__error">{{ $message }}</div>
                @enderror
            </div>

            <!-- 登録ボタン -->
            <button type="submit" class="register__submit">登録する</button>
        </form>

        <!-- ログインリンク -->
        <div class="register__login-link">
            <a href="/login" class="register__link">ログインはこちら</a>
        </div>
    </div>
@endsection
