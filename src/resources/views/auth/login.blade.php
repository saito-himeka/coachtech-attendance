@extends('layouts.app')

@section('title', 'ログイン - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@stop

{{-- ログイン前なので、ヘッダーナビは空 --}}
@section('header-nav')
@endsection

@section('content')
<div class="login-container">
    <h2 class="form-title">ログイン</h2>

    <form action="{{ route('login') }}" method="POST" class="login-form" novalidate>
        @csrf
        
        {{-- メールアドレス --}}
        <div class="form-group">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" >
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror" >
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- 送信ボタン --}}
        <div class="form-action">
            <button type="submit" class="submit-btn">ログインする</button>
        </div>

        {{-- 会員登録ページへのリンク --}}
        <div class="form-footer">
            <a href="{{ route('register') }}" class="register-link">会員登録はこちら</a>
        </div>
    </form>
</div>
@endsection