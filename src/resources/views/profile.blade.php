@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('header-items')
    @include('components.header-nav')
@endsection

@section('content')
    <div class="profile__container">
        <h1 class="profile__title">プロフィール設定</h1>

        <form class="profile__form">
            <div class="profile__avatar">
                <div class="profile__avatar-image"></div>
                <button type="button" class="profile__avatar-button">画像を選択する</button>
            </div>

            <div class="profile__form-group">
                <label for="username" class="profile__label">ユーザー名</label>
                <input type="text" id="username" name="username" class="profile__input">
            </div>

            <div class="profile__form-group">
                <label for="postal_code" class="profile__label">郵便番号</label>
                <input type="text" id="postal_code" name="postal_code" class="profile__input">
            </div>

            <div class="profile__form-group">
                <label for="address" class="profile__label">住所</label>
                <input type="text" id="address" name="address" class="profile__input">
            </div>

            <div class="profile__form-group">
                <label for="building" class="profile__label">建物名</label>
                <input type="text" id="building" name="building" class="profile__input">
            </div>

            <button type="submit" class="profile__submit-button">更新する</button>
        </form>
    </div>
@endsection
