@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('header-items')
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

    <div class="index__tab-container">
        <div class="index__tabs">
            <a href="?{{ http_build_query(array_filter(array_merge(request()->query(), ['tab' => null]))) }}"
                class="index__tab {{ request('tab') != 'mylist' ? 'index__tab--active' : '' }}">おすすめ</a>
            <a href="?{{ http_build_query(array_merge(request()->query(), ['tab' => 'mylist'])) }}"
                class="index__tab {{ request('tab') == 'mylist' ? 'index__tab--active' : '' }}">マイリスト</a>
        </div>
    </div>

    <div class="index__items">
        @foreach ($items as $item)
            <div class="index__item">
                <a href="/item/{{ $item->id }}" class="index__item-link">
                    <div class="index__item-image">
                        @if ($item->image_path)
                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                        @endif
                        @if ($item->purchaser_id !== null)
                            <div class="index__sold-label">SOLD</div>
                        @endif
                    </div>
                    <div class="index__item-name">{{ $item->name }}</div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
