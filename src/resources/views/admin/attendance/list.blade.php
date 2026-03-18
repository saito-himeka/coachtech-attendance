@extends('layouts.app')

@section('title', '勤怠一覧 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
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
<div class="attendance-container">
    <h2 class="page-title">2023年6月1日の勤怠</h2>

    <div class="date-pager">
        <a href="#" class="pager-btn">← 前日</a>
        <span class="current-date">
            <span class="calendar-icon">📅</span> 2023/06/01
        </span>
        <a href="#" class="pager-btn">翌日 →</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->name }}</td>
                <td>{{ $attendance->start_time }}</td>
                <td>{{ $attendance->end_time }}</td>
                <td>{{ $attendance->break_time }}</td>
                <td>{{ $attendance->total_time }}</td>
                <td><a href="/attendance/{{ $attendance->id }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection