@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('header-nav')
    @include('components.header-nav')
@endsection

@section('content')
    {{-- メッセージ表示 --}}
    @if (session('success'))
        <div class="message message--success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="message message--error">
            {{ session('error') }}
        </div>
    @endif

    {{-- タブセクション --}}
    <div class="index__tab-container">
        <div class="index__tabs">
            <a href="?{{ http_build_query(array_filter(array_merge(request()->query(), ['tab' => null]))) }}"
                class="index__tab {{ request('tab') != 'mylist' ? 'index__tab--active' : '' }}">おすすめ</a>
            <a href="?{{ http_build_query(array_merge(request()->query(), ['tab' => 'mylist'])) }}"
                class="index__tab {{ request('tab') == 'mylist' ? 'index__tab--active' : '' }}">マイリスト</a>
        </div>
    </div>

    {{-- 商品セクション --}}
    <div class="index__items">
        @foreach ($items as $item)
            @include('components.item', ['item' => $item])
        @endforeach
    </div>
@endsection
