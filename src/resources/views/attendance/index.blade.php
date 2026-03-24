@extends('layouts.app')

@section('title', '勤怠登録 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@stop


@section('content')
<div class="punch-container">
    {{-- ステータスバッジ（全て統一色） --}}
    <div class="status-badge">
        @if ($status === 'not_working')
            <span class="badge">勤務外</span>
        @elseif ($status === 'working')
            <span class="badge">出勤中</span>
        @elseif ($status === 'on_break')
            <span class="badge">休憩中</span>
        @elseif ($status === 'clocked_out')
            <span class="badge">退勤済</span>
        @endif
    </div>

    {{-- 現在の日付（JavaScript で更新） --}}
    <div class="current-date" id="currentDate">
        2023年6月1日(木)
    </div>

    {{-- 現在の時刻（JavaScript で更新） --}}
    <div class="current-time" id="currentTime">
        08:00
    </div>

    {{-- 打刻ボタン（ステータスに応じて切り替え） --}}
    @if ($status === 'not_working')
        {{-- 出勤ボタン --}}
        <form action="{{ route('attendance.clock-in') }}" method="POST" class="punch-form">
            @csrf
            <button type="submit" class="punch-btn">出勤</button>
        </form>
    @elseif ($status === 'working')
        {{-- 休憩入・退勤ボタン --}}
        <div class="button-group">
            <form action="{{ route('attendance.clock-out') }}" method="POST" class="punch-form">
                @csrf
                <button type="submit" class="punch-btn">退勤</button>
            </form>
            <form action="{{ route('attendance.break-start') }}" method="POST" class="punch-form">
                @csrf
                <button type="submit" class="punch-btn punch-btn-white">休憩入</button>
            </form>
        </div>
    @elseif ($status === 'on_break')
        {{-- 休憩戻ボタン（白地に黒文字） --}}
        <form action="{{ route('attendance.break-end') }}" method="POST" class="punch-form">
            @csrf
            <button type="submit" class="punch-btn punch-btn-white">休憩戻</button>
        </form>
    @elseif ($status === 'clocked_out')
        {{-- 退勤済みの場合、ボタンなし --}}
        <p class="message-clocked-out">お疲れ様でした。</p>
    @endif
</div>

{{-- JavaScript: 日時のリアルタイム更新 --}}
<script>
function updateDateTime() {
    const now = new Date();
    
    // 日付表示（例: 2026年3月5日(水)）
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const date = now.getDate();
    const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    const weekday = weekdays[now.getDay()];
    
    const dateElement = document.getElementById('currentDate');
    if (dateElement) {
        dateElement.textContent = `${year}年${month}月${date}日(${weekday})`;
    }
    
    // 時刻表示（例: 14:30）
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        timeElement.textContent = `${hours}:${minutes}`;
    }
}

// 初回実行 + 1秒ごとに更新
updateDateTime();
setInterval(updateDateTime, 1000);
</script>
@endsection