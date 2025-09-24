@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('header-nav')
    @include('components.header-nav')
@endsection


@section('content')
    <div class="mypage__container">
        <!-- ユーザー情報セクション -->
        <div class="mypage__user-info">
            <div class="mypage__user-content">
                <div class="mypage__avatar">
                    @if (auth()->user()->image_path)
                        <img src="{{ asset('storage/' . auth()->user()->image_path) }}" alt="プロフィール画像"
                            class="mypage__avatar-img">
                    @else
                        <div class="mypage__avatar-placeholder"></div>
                    @endif
                </div>
                <div class="mypage__user-name">{{ auth()->user()->name }}</div>
            </div>
            <a href="/mypage/profile" class="mypage__profile-button">プロフィールを編集</a>
        </div>

        <!-- タブセクション -->
        <div class="mypage__tab-container">
            <div class="mypage__tabs">
                <a href="?page=sell"
                    class="mypage__tab {{ request('page') == 'sell' || !request('page') ? 'mypage__tab--active' : '' }}">
                    出品した商品
                </a>
                <a href="?page=buy" class="mypage__tab {{ request('page') == 'buy' ? 'mypage__tab--active' : '' }}">
                    購入した商品
                </a>
            </div>
        </div>

        <!-- 商品一覧セクション -->
        <div class="mypage__items">
            @if (request('page') == 'buy')
                {{-- 購入した商品を表示 --}}
                @foreach ($purchasedItems as $item)
                    @include('components.item', ['item' => $item])
                @endforeach
            @else
                {{-- 出品した商品を表示（デフォルト） --}}
                @foreach ($soldItems as $item)
                    @include('components.item', ['item' => $item])
                @endforeach
            @endif
        </div>
    </div>

@endsection
