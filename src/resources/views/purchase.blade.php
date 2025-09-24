@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('header-nav')
    @include('components.header-nav')
@endsection


@section('content')
    <div class="purchase__container">
        {{-- 左側：商品情報と配送・支払い情報 --}}
        <div class="purchase__left">
            {{-- 商品情報セクション --}}
            <div class="purchase__item">
                <div class="purchase__item-image-wrapper">
                    @if ($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}"
                            class="purchase__item-image">
                    @else
                        <div class="purchase__item-image-placeholder">商品画像</div>
                    @endif
                </div>
                <div class="purchase__item-info">
                    <h2 class="purchase__item-name">{{ $item->name }}</h2>
                    <p class="purchase__item-price">¥ {{ number_format($item->price) }}</p>
                </div>
            </div>

            {{-- 支払い方法セクション --}}
            <div class="purchase__payment">
                <h3 class="purchase__section-title">支払い方法</h3>
                <div class="purchase__payment-wrapper">
                    <select name="payment_method" class="purchase__payment-select" required>
                        <option value="" {{ old('payment_method') == '' ? 'selected' : '' }} disabled hidden>選択してください
                        </option>
                        <option value="konbini" {{ old('payment_method') == 'konbini' ? 'selected' : '' }}>コンビニ払い</option>
                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>カード支払い</option>
                    </select>
                </div>
                @error('payment_method')
                    <p class="purchase__error">{{ $message }}</p>
                @enderror
            </div>

            {{-- 配送先セクション --}}
            <div class="purchase__shipping">
                <div class="purchase__shipping-header">
                    <h3 class="purchase__section-title">配送先</h3>
                    <a href="/purchase/address/{{ $item->id }}" class="purchase__shipping-change">変更する</a>
                </div>
                <div class="purchase__shipping-info">
                    <p class="purchase__shipping-address">〒 {{ $shippingAddress['postal_code'] ?: '' }}<br>
                        {{ $shippingAddress['address'] ?: '未設定' }}
                        @if ($shippingAddress['building'])
                            {{ $shippingAddress['building'] }}
                        @endif
                    </p>
                </div>
                @error('shipping_address')
                    <p class="purchase__error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- 右側：購入サマリー --}}
        <div class="purchase__right">
            <form class="purchase__form" action="/purchase/{{ $item->id }}" method="POST">
                @csrf
                <div class="purchase__summary">
                    <table class="purchase__summary-table">
                        <tr class="purchase__summary-row">
                            <td class="purchase__summary-label">商品代金</td>
                            <td class="purchase__summary-value">¥ {{ number_format($item->price) }}</td>
                        </tr>
                        <tr class="purchase__summary-row">
                            <td class="purchase__summary-label">支払い方法</td>
                            <td class="purchase__summary-value purchase__payment-display">選択してください</td>
                        </tr>
                    </table>
                </div>

                <input type="hidden" name="payment_method" class="purchase__payment-hidden"
                    value="{{ old('payment_method') }}">
                <button type="submit" class="purchase__submit-button">購入する</button>
            </form>
        </div>
    </div>

    <script>
        // ページ読み込み時に初期表示を設定
        document.addEventListener('DOMContentLoaded', function() {
            const selectElement = document.querySelector('.purchase__payment-select');
            const displayElement = document.querySelector('.purchase__payment-display');
            const hiddenInput = document.querySelector('.purchase__payment-hidden');

            // 初期値を設定
            updatePaymentDisplay(selectElement.value);
            hiddenInput.value = selectElement.value;
        });

        // 支払い方法が変更されたときにサマリーを更新
        document.querySelector('.purchase__payment-select').addEventListener('change', function() {
            const hiddenInput = document.querySelector('.purchase__payment-hidden');

            // 隠しフィールドに値をセット
            hiddenInput.value = this.value;

            updatePaymentDisplay(this.value);
        });

        // 表示を更新する関数
        function updatePaymentDisplay(value) {
            const displayElement = document.querySelector('.purchase__payment-display');

            if (value === 'konbini') {
                displayElement.textContent = 'コンビニ払い';
            } else if (value === 'card') {
                displayElement.textContent = 'カード支払い';
            } else {
                displayElement.textContent = '選択してください'; // デフォルト
            }
        }
    </script>
@endsection
