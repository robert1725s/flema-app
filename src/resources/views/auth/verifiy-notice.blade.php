@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/verification.css') }}">
@endsection

@section('content')
    <div class="verification">
        <div class="verification__container">
            <div class="verification__message">
                <p class="verification__message-text">
                    登録していただいたメールアドレスに認証メールを送付しました。<br>
                    メール認証を完了してください。
                </p>
            </div>

            <div class="verification__actions">
                <a href="#" class="verification__verify-button">認証はこちらから</a>
                <a href="#" class="verification__resend-link">認証メールを再送する</a>
            </div>
        </div>
    </div>
@endsection
