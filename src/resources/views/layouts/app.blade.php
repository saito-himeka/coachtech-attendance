<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <div class="app-wrapper">
        <header class="header">
            <div class="header__inner">
                <a href="/" class="header__logo"><img src="{{ asset('img/logo.png') }}" alt="COACHTECH"></a>
                
                {{-- ナビゲーション --}}
                <nav class="header__nav">
                    @auth
                        <ul class="nav-list">
                            @if(auth()->user()->role == 1)
                                {{-- 管理者用ナビゲーション --}}
                                <li class="nav-item"><a href="/admin/attendance/list">勤怠一覧</a></li>
                                <li class="nav-item"><a href="/admin/staff/list">スタッフ一覧</a></li>
                                <li class="nav-item"><a href="{{ route('admin.stamp_correction_request.list') }}">申請一覧</a></li>
                            @else
                                {{-- 一般ユーザー用ナビゲーション --}}
                                <li class="nav-item"><a href="{{ route('attendance.index') }}">勤怠</a></li>
                                <li class="nav-item"><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                                <li class="nav-item"><a href="{{ route('stamp_correction_request.list') }}">申請</a></li>
                            @endif
                            
                            {{-- ログアウト（共通） --}}
                            <li class="nav-item">
                                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button class="logout-btn">ログアウト</button>
                                </form>
                            </li>
                        </ul>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="main">
            @yield('content')
        </main>
    </div>
</body>
</html>