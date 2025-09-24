@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endsection

@section('header-nav')
    @include('components.header-nav')
@endsection


@section('content')
    <div class="sell__container">
        <h1 class="sell__title">商品の出品</h1>

        <form class="sell__form" method="POST" action="/sell" enctype="multipart/form-data">
            @csrf
            <!-- 商品画像セクション -->
            <div class="sell__section">
                <label class="sell__label">商品画像</label>
                <div class="sell__image-upload">
                    <div class="sell__image-upload-area">
                        <input type="file" name="image" class="sell__file-input" accept="image/*"
                            onchange="previewImage()">
                        <label class="sell__upload-button">画像を選択する</label>
                        <div class="sell__image-preview">
                            <img class="sell__preview-img">
                            <div class="sell__filename"></div>
                        </div>
                    </div>
                    @error('image')
                        <div class="sell__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- 商品の詳細セクション -->
            <div class="sell__section">
                <h2 class="sell__section-title">商品の詳細</h2>
                <div class="sell__form-group">
                    <label class="sell__label">カテゴリー</label>
                    <div class="sell__category-tags">
                        @foreach ($categories as $category)
                            <label class="sell__category-tag">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                    class="sell__category-checkbox"
                                    {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
                                <span class="sell__category-text">{{ $category->content }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('categories')
                        <div class="sell__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sell__form-group">
                    <label class="sell__label">商品の状態</label>
                    <div class="sell__select-wrapper">
                        <select class="sell__select" name="condition">
                            <option value="" selected disabled hidden>選択してください</option>
                            <option value="1" {{ old('condition') == '1' ? 'selected' : '' }}>良好</option>
                            <option value="2" {{ old('condition') == '2' ? 'selected' : '' }}>目立った傷や汚れなし</option>
                            <option value="3" {{ old('condition') == '3' ? 'selected' : '' }}>やや傷や汚れあり</option>
                            <option value="4" {{ old('condition') == '4' ? 'selected' : '' }}>状態が悪い</option>
                        </select>
                    </div>
                    @error('condition')
                        <div class="sell__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- 商品名と説明セクション -->
            <div class="sell__section">
                <h2 class="sell__section-title">商品名と説明</h2>

                <div class="sell__form-group">
                    <label class="sell__label">商品名</label>
                    <input type="text" name="name" class="sell__input" value="{{ old('name') }}">
                    @error('name')
                        <div class="sell__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sell__form-group">
                    <label class="sell__label">ブランド名</label>
                    <input type="text" name="brand" class="sell__input" value="{{ old('brand') }}">
                    @error('brand')
                        <div class="sell__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sell__form-group">
                    <label class="sell__label">商品の説明</label>
                    <textarea name="description" class="sell__textarea">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="sell__error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 販売価格セクション -->
                <div class="sell__form-group">
                    <label class="sell__label">販売価格</label>
                    <div class="sell__price-input">
                        <span class="sell__price-symbol">¥</span>
                        <input name="price" class="sell__input sell__input--price" value="{{ old('price') }}">
                    </div>
                    @error('price')
                        <div class="sell__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>



            <!-- 出品ボタン -->
            <button type="submit" class="sell__submit-button">
                出品する
            </button>
        </form>
    </div>

    <!-- 商品画像プレビュー機能 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadButton = document.querySelector('.sell__upload-button');
            const fileInput = document.querySelector('.sell__file-input');

            // ボタンクリックでファイル選択ダイアログを開く
            uploadButton.addEventListener('click', function() {
                fileInput.click();
            });
        });

        function previewImage() {
            const input = document.querySelector('.sell__file-input');
            const preview = document.querySelector('.sell__preview-img');
            const previewDiv = document.querySelector('.sell__image-preview');
            const filename = document.querySelector('.sell__filename');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    filename.textContent = file.name;
                    previewDiv.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
