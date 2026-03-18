@extends('layouts.app')

@section('title', '管理者ログイン - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@stop

@section('content')
<div class="login-container">
    <h2 class="login-title">管理者ログイン</h2>
    
    <form action="{{ route('admin.login') }}" method="POST" class="login-form" novalidate>
        @csrf
        
        <!-- メールアドレス -->
        <div class="form-group">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <!-- パスワード -->
        <div class="form-group">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror" >
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <!-- 送信ボタン -->
        <div class="form-button">
            <button type="submit" class="submit-btn">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection