@extends('layouts.app')

@section('title', '会員登録 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@stop

{{-- ログイン前なので、ヘッダーナビは空にするか、ログインリンクのみ表示 --}}
@section('header-nav')
{{-- 空、もしくは必要に応じてログインページへのリンク --}}
@endsection

@section('content')
<div class="register-container">
    <h2 class="form-title">会員登録</h2>

    <form action="{{ route('register') }}" method="POST" class="register-form" novalidate>
        @csrf
        
        {{-- 名前 --}}
        <div class="form-group">
            <label for="name" class="form-label">名前</label>
            <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" >
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

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

        {{-- パスワード確認 --}}
        <div class="form-group">
            <label for="password_confirmation" class="form-label">パスワード確認</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" >
        </div>

        {{-- 送信ボタン --}}
        <div class="form-action">
            <button type="submit" class="submit-btn">登録する</button>
        </div>

        {{-- ログインページへのリンク --}}
        <div class="form-footer">
            <a href="{{ route('login') }}" class="login-link">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection