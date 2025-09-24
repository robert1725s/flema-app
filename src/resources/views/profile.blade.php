@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('header-nav')
    @include('components.header-nav')
@endsection


@section('content')
    <div class="profile__container">
        <h1 class="profile__title">プロフィール設定</h1>

        <form class="profile__form" action="/mypage/profile" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- プロフィール画像 -->
            <div class="profile__avatar">
                <div class="profile__avatar-image">
                    @if (auth()->user()->image_path)
                        <img src="{{ asset('storage/' . auth()->user()->image_path) }}" alt="プロフィール画像"
                            class="profile__avatar-img profile__preview-image">
                    @else
                        <div class="profile__avatar-placeholder"></div>
                        <img src="" alt="プレビュー画像" class="profile__avatar-img profile__preview-image"
                            style="display: none;">
                    @endif
                </div>
                <input type="file" name="profile_image" class="profile__file-input" accept="image/jpeg,image/png"
                    onchange="previewImage(event)">
                <button type="button" class="profile__avatar-button"
                    onclick="document.querySelector('.profile__file-input').click()">画像を選択する</button>
            </div>
            @error('profile_image')
                <p class="profile__error">{{ $message }}</p>
            @enderror

            <!-- ユーザー名 -->
            <div class="profile__form-group">
                <label class="profile__label">ユーザー名</label>
                <input type="text" name="name" class="profile__input" value="{{ old('name', auth()->user()->name) }}">
                @error('name')
                    <p class="profile__error">{{ $message }}</p>
                @enderror
            </div>

            <!-- 郵便番号 -->
            <div class="profile__form-group">
                <label class="profile__label">郵便番号</label>
                <input type="text" name="postal_code" class="profile__input"
                    value="{{ old('postal_code', auth()->user()->postal_code) }}">
                @error('postal_code')
                    <p class="profile__error">{{ $message }}</p>
                @enderror
            </div>

            <!-- 住所 -->
            <div class="profile__form-group">
                <label class="profile__label">住所</label>
                <input type="text" name="address" class="profile__input"
                    value="{{ old('address', auth()->user()->address) }}">
                @error('address')
                    <p class="profile__error">{{ $message }}</p>
                @enderror
            </div>

            <!-- 建物名 -->
            <div class="profile__form-group">
                <label class="profile__label">建物名</label>
                <input type="text" name="building" class="profile__input"
                    value="{{ old('building', auth()->user()->building) }}">
                @error('building')
                    <p class="profile__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="profile__submit-button">更新する</button>
        </form>
    </div>

    <!-- プロフィール画像プレビュー機能 -->
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const placeholder = document.querySelector('.profile__avatar-placeholder');
            const previewImage = document.querySelector('.profile__preview-image');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    // プレースホルダーを非表示
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }

                    // プレビュー画像を表示
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                };

                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
