@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <div class="login__container">
        <h1 class="login__title">ログイン</h1>
        <form class="login__form">
            <div class="login__form-group">
                <label for="email" class="login__label">メールアドレス</label>
                <input type="email" id="email" name="email" class="login__input" required>
            </div>
            <div class="login__form-group login__form-group--password">
                <label for="password" class="login__label">パスワード</label>
                <input type="password" id="password" name="password" class="login__input" required>
            </div>
            <button type="submit" class="login__button">ログインする</button>
        </form>
        <div class="login__register">
            <a href="#" class="login__register-link">会員登録はこちら</a>
        </div>
    </div>
@endsection
