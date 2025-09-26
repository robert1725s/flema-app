@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notice.css') }}">
@endsection

@section('content')
    @if (session('status') == 'verification-link-sent')
        <div class="notice__success-message">
            認証メールが再送されました
        </div>
    @endif
    <div class="notice__container">

        <!-- メッセージ -->
        <div class="notice__message">
            <p class="notice__text">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください。
            </p>
        </div>


        <div class="notice__actions">
            <!-- Mailhogへのリンク -->
            <a href="{{ config('services.mailhog.url') }}" class="notice__button">認証はこちらから</a>
            <form method="POST" action="{{ route('verification.send') }}" class="notice__form">
                @csrf
                <!-- 再送ボタン -->
                <button type="submit" class="notice__link">認証メールを再送する</button>
            </form>
        </div>
    </div>
@endsection
