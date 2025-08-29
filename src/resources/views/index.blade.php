@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('header-items')
    @include('components.header-nav')
@endsection

@section('content')
    <div class="index__tab-container">
        <div class="index__tabs">
            <a href="#" class="index__tab index__tab--active">おすすめ</a>
            <a href="#" class="index__tab">マイリスト</a>
        </div>
    </div>

    <div class="index__items">
        <div class="index__item">
            <div class="index__item-image">
                商品画像
            </div>
            <p class="index__item-name">商品名</p>
        </div>

        <div class="index__item">
            <div class="index__item-image">
                商品画像
            </div>
            <p class="index__item-name">商品名</p>
        </div>

        <div class="index__item">
            <div class="index__item-image">
                商品画像
            </div>
            <p class="index__item-name">商品名</p>
        </div>

        <div class="index__item">
            <div class="index__item-image">
                商品画像
            </div>
            <p class="index__item-name">商品名</p>
        </div>

        <div class="index__item">
            <div class="index__item-image">
                商品画像
            </div>
            <p class="index__item-name">商品名</p>
        </div>
    </div>
@endsection
