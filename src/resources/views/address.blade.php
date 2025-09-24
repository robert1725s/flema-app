@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('header-nav')
    @include('components.header-nav')
@endsection


@section('content')
    <div class="address__container">
        <h1 class="address__title">住所の変更</h1>

        <form class="address__form" action="/purchase/address/{{ $item_id }}" method="POST">
            @csrf
            {{-- 郵便番号 --}}
            <div class="address__field">
                <label class="address__label">郵便番号</label>
                <input type="text" name="postal_code" class="address__input"
                    value="{{ old('postal_code', $currentAddress['postal_code']) }}" placeholder="">
                @error('postal_code')
                    <p class="address__error">{{ $message }}</p>
                @enderror
            </div>

            {{-- 住所 --}}
            <div class="address__field">
                <label class="address__label">住所</label>
                <input type="text" name="address" class="address__input"
                    value="{{ old('address', $currentAddress['address']) }}" placeholder="">
                @error('address')
                    <p class="address__error">{{ $message }}</p>
                @enderror
            </div>

            {{-- 建物名 --}}
            <div class="address__field">
                <label class="address__label">建物名</label>
                <input type="text" name="building" class="address__input"
                    value="{{ old('building', $currentAddress['building']) }}" placeholder="">
                @error('building')
                    <p class="address__error">{{ $message }}</p>
                @enderror
            </div>

            {{-- 更新ボタン --}}
            <button type="submit" class="address__submit-button">更新する</button>
        </form>
    </div>
@endsection
