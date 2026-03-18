@extends('layouts.app')

@section('title', 'スタッフ勤怠 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_attendance.css') }}">
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
<div class="staff-attendance-container">
    <h2 class="page-title">西玲奈さんの勤怠</h2>

    <div class="month-pager">
        <a href="#" class="pager-btn">← 前月</a>
        <span class="current-month">
            <span class="calendar-icon">📅</span> 2023/06
        </span>
        <a href="#" class="pager-btn">翌月 →</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthly_attendances as $record)
            <tr>
                <td>{{ $record['date'] }}</td>
                <td>{{ $record['start_time'] }}</td>
                <td>{{ $record['end_time'] }}</td>
                <td>{{ $record['break_time'] }}</td>
                <td>{{ $record['total_time'] }}</td>
                <td><a href="/admin/attendance/detail/{{ $record['id'] }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="csv-action">
        <button class="csv-btn">CSV出力</button>
    </div>
</div>
@endsection