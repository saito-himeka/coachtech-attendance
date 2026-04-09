@extends('layouts.app')

@section('title', 'メール認証 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/varify.css') }}">
@stop

{{-- 認証前なのでナビゲーションは表示させない --}}
@section('header-nav')
@endsection

@section('content')
    <div class="varify-container">
        @if (session('status') == 'verification-link-sent')
            <div class="status-message">
                新しい認証リンクを、登録したメールアドレスに送信しました。
            </div>
        @endif

        <div class="message">
            登録していただいたメールアドレスに認証メールを送信しました。<br>
            メール認証を完了してください。
        </div>

        <a href="http://localhost:8025" target="_blank" class="btn-verify">
            認証はこちらから
        </a>

        <div class="resend-block">
            <form class="resend-form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-resend">
                    認証メールを再送する
                </button>
            </form>
        </div>
    </div>
@endsection