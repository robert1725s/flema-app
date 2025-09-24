@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('header-nav')
    @include('components.header-nav')
@endsection

@section('content')
    <div class="detail__container">
        {{-- 左側：商品画像セクション --}}
        <div class="detail__image-section">
            @if ($item->image_path)
                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="detail__image">
            @else
                <div class="detail__image-placeholder">商品画像</div>
            @endif
        </div>

        {{-- 右側：商品情報セクション --}}
        <div class="detail__info-section">
            {{-- 商品名 --}}
            <h1 class="detail__title">{{ $item->name }}</h1>

            {{-- ブランド名 --}}
            @if ($item->brand)
                <p class="detail__brand">{{ $item->brand }}</p>
            @endif

            {{-- 価格 --}}
            <p class="detail__price">
                <span class="detail__price-symbol">¥</span>{{ number_format($item->price) }}
                <span class="detail__price-tax">(税込)</span>
            </p>

            {{-- いいね・コメントボタン --}}
            <div class="detail__actions">
                <form action="/item/favorite/{{ $item->id }}" method="POST" class="detail__action-form">
                    @csrf
                    <button type="submit" class="detail__action-button detail__action-button--favorite">
                        @if ($item->isFavoritedBy(auth()->user()))
                            <i class="fas fa-star detail__action-icon detail__action-icon--active"></i>
                        @else
                            <i class="far fa-star detail__action-icon"></i>
                        @endif
                        <span class="detail__action-count">{{ $item->favorites_count ?? 0 }}</span>
                    </button>
                </form>

                <button type="button" class="detail__action-button detail__action-button--comment"
                    onclick="document.querySelector('.detail__comments').scrollIntoView();">
                    <div class="detail__action-icon"></div>
                    <span class="detail__action-count">{{ $item->comments_count ?? 0 }}</span>
                </button>
            </div>

            {{-- 購入ボタン --}}
            @if ($item->purchaser_id)
                <button type="button" class="detail__purchase-button detail__purchase-button--disabled"
                    disabled>売り切れ</button>
            @elseif ($item->seller_id === auth()->id())
                <button type="button" class="detail__purchase-button detail__purchase-button--disabled"
                    disabled>出品した商品</button>
            @else
                <form action="/purchase/{{ $item->id }}" method="GET">
                    <button type="submit" class="detail__purchase-button">購入手続きへ</button>
                </form>
            @endif

            {{-- 商品説明 --}}
            <div class="detail__description">
                <h2 class="detail__section-title">商品説明</h2>
                <p class="detail__description-text">{{ $item->description }}</p>
            </div>

            {{-- 商品の情報 --}}
            <div class="detail__info">
                <h2 class="detail__section-title">商品の情報</h2>

                {{-- カテゴリー --}}
                <div class="detail__info-row">
                    <span class="detail__info-label">カテゴリー</span>
                    <div class="detail__info-value">
                        @if ($item->categories && $item->categories->count() > 0)
                            @foreach ($item->categories as $category)
                                <span class="detail__category-tag">{{ $category->content }}</span>
                            @endforeach
                        @else
                            <span class="detail__category-tag">未設定</span>
                        @endif
                    </div>
                </div>

                {{-- 状態 --}}
                <div class="detail__info-row">
                    <span class="detail__info-label">商品の状態</span>
                    <span class="detail__info-value">
                        @switch($item->condition)
                            @case(1)
                                良好
                            @break

                            @case(2)
                                目立った傷や汚れなし
                            @break

                            @case(3)
                                やや傷や汚れあり
                            @break

                            @case(4)
                                傷や汚れあり
                            @break

                            @default
                                -
                        @endswitch
                    </span>
                </div>
            </div>

            {{-- コメントセクション --}}
            <div class="detail__comments">
                <h2 class="detail__comments-title">コメント({{ $item->comments_count ?? 0 }})</h2>

                {{-- 既存のコメント表示 --}}
                @if ($item->comments && $item->comments->count() > 0)
                    @foreach ($item->comments as $comment)
                        <div class="detail__comment-item">
                            <div class="detail__comment-user">
                                @if ($comment->user->image_path)
                                    <img src="{{ asset('storage/' . $comment->user->image_path) }}"
                                        alt="{{ $comment->user->name }}" class="detail__comment-avatar">
                                @else
                                    <div class="detail__comment-avatar detail__comment-avatar--placeholder"></div>
                                @endif
                                <span class="detail__comment-username">{{ $comment->user->name }}</span>
                            </div>
                            <div class="detail__comment-text">{{ $comment->content }}</div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- 商品へのコメント入力フォーム --}}
            <div class="detail__comment-form">
                <h3 class="detail__form-title">商品へのコメント</h3>
                <form action="/item/comment/{{ $item->id }}" method="POST">
                    @csrf
                    <textarea name="comment" class="detail__comment-textarea"></textarea>
                    @error('comment')
                        <p class="detail__error">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="detail__comment-submit">コメントを送信する</button>
                </form>
            </div>
        </div>
    </div>
@endsection
