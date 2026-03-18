@extends('layouts.app')

@section('title', 'スタッフ一覧 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">
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
<div class="staff-container">
    <h2 class="page-title">スタッフ一覧</h2>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staffs as $staff)
            <tr>
                <td>{{ $staff->name }}</td>
                <td>{{ $staff->email }}</td>
                <td><a href="/admin/attendance/staff/{{ $staff->id }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection