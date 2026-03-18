@extends('layouts.app')

@section('title', '申請一覧 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/list.css') }}">
@stop

@section('header-nav')
<ul class="nav-list">
    <li class="nav-item"><a href="/admin/attendance">勤怠一覧</a></li>
    <li class="nav-item"><a href="/admin/staff">スタッフ一覧</a></li>
    <li class="nav-item"><a href="/admin/request">申請一覧</a></li>
    <li class="nav-item">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="logout-btn">ログアウト</button>
        </form>
    </li>
</ul>
@endsection

@section('content')
<div class="request-container">
    <h2 class="page-title">申請一覧</h2>

    {{-- タブ切り替えエリア --}}
    <div class="tab-menu">
        <a href="#" class="tab-item is-active">承認待ち</a>
        <a href="#" class="tab-item">承認済み</a>
    </div>

    {{-- 申請一覧テーブル --}}
    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            {{-- ここでは例として「承認待ち」のデータをループ表示する想定 --}}
            @foreach($requests as $request)
            <tr>
                <td>{{ $request->status }}</td>
                <td>{{ $request->user_name }}</td>
                <td>{{ $request->target_date }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ $request->requested_at }}</td>
                <td><a href="/admin/request/detail/{{ $request->id }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection