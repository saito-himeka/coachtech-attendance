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
                {{-- ここにナビゲーションが入る --}}
                <nav class="header__nav">
                    @yield('header-nav')
                </nav>
            </div>
        </header>

        <main class="main">
            @yield('content')
        </main>
    </div>
</body>
</html>