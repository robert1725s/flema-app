@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <div class="login__container">
        <h1 class="login__title">ログイン</h1>
        <form class="login__form" action="/login" method="POST">
            @csrf

            <!-- メールアドレス -->
            <div class="login__form-group">
                <label for="email" class="login__label">メールアドレス</label>
                <input name="email" class="login__input" value="{{ old('email') }}">
                @error('email')
                    <div class="login__error">{{ $message }}</div>
                @enderror
            </div>

            <!-- パスワード -->
            <div class="login__form-group login__form-group--password">
                <label for="password" class="login__label">パスワード</label>
                <input type="password" name="password" class="login__input">
                @error('password')
                    <div class="login__error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="login__button">ログインする</button>
        </form>
        <div class="login__register">
            <a href="/register" class="login__register-link">会員登録はこちら</a>
        </div>
    </div>
@endsection
