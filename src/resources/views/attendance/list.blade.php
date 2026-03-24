@extends('layouts.app')

@section('title', '勤怠一覧 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@stop


@section('content')
<div class="attendance-container">
    <h2 class="page-title">勤怠一覧</h2>

    {{-- 月選択・切替 --}}
    <div class="month-pager">
        {{-- 前月ボタン --}}
        <a href="{{ route('attendance.list', [
            'year' => $month == 1 ? $year - 1 : $year,
            'month' => $month == 1 ? 12 : $month - 1
        ]) }}" class="pager-btn">← 前月</a>
        
        {{-- 現在の月表示 --}}
        <span class="current-month">
            <span class="calendar-icon">📅</span> {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
        </span>
        
        {{-- 翌月ボタン --}}
        <a href="{{ route('attendance.list', [
            'year' => $month == 12 ? $year + 1 : $year,
            'month' => $month == 12 ? 1 : $month + 1
        ]) }}" class="pager-btn">翌月 →</a>
    </div>

    {{-- 勤怠履歴テーブル --}}
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
            @php
                // その月の日数を取得
                $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
                
                // 勤怠データを日付でインデックス化（文字列に変換）
                $attendanceByDate = [];
                foreach($attendances as $attendance) {
                    // Carbonオブジェクトの場合は文字列に変換
                    $dateKey = $attendance->date instanceof \Carbon\Carbon 
                        ? $attendance->date->format('Y-m-d') 
                        : $attendance->date;
                    $attendanceByDate[$dateKey] = $attendance;
                }
                
                // 曜日配列
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            @endphp
            
            {{-- 1日から月末まで全ての日付を表示 --}}
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    // 日付を生成
                    $date = \Carbon\Carbon::createFromDate($year, $month, $day);
                    $dateString = $date->format('Y-m-d');
                    $weekday = $weekdays[$date->dayOfWeek];
                    
                    // その日の勤怠データを取得（なければnull）
                    $attendance = $attendanceByDate[$dateString] ?? null;
                    
                    // 休憩時間と勤務時間を計算
                    $totalBreakMinutes = 0;
                    if ($attendance) {
                        foreach($attendance->restTimes as $rest) {
                            if($rest->start_time && $rest->end_time) {
                                $start = \Carbon\Carbon::parse($rest->start_time);
                                $end = \Carbon\Carbon::parse($rest->end_time);
                                $totalBreakMinutes += $start->diffInMinutes($end);
                            }
                        }
                    }
                @endphp
                
                <tr>
                    {{-- 日付 --}}
                    <td>{{ $date->format('m/d') }}({{ $weekday }})</td>
                    
                    {{-- 出勤時刻 --}}
                    <td>
                        @if($attendance && $attendance->start_time)
                            {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}
                        @endif
                    </td>
                    
                    {{-- 退勤時刻 --}}
                    <td>
                        @if($attendance && $attendance->end_time)
                            {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
                        @endif
                    </td>
                    
                    {{-- 休憩時間 --}}
                    <td>
                        @if($totalBreakMinutes > 0)
                            @php
                                $hours = floor($totalBreakMinutes / 60);
                                $minutes = $totalBreakMinutes % 60;
                            @endphp
                            {{ $hours }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>
                    
                    {{-- 勤務時間（合計） --}}
                    <td>
                        @if($attendance && $attendance->start_time && $attendance->end_time)
                            @php
                                $start = \Carbon\Carbon::parse($attendance->start_time);
                                $end = \Carbon\Carbon::parse($attendance->end_time);
                                $totalMinutes = $start->diffInMinutes($end);
                                $workMinutes = $totalMinutes - $totalBreakMinutes;
                                $workHours = floor($workMinutes / 60);
                                $workMins = $workMinutes % 60;
                            @endphp
                            {{ $workHours }}:{{ str_pad($workMins, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>
                    
                    {{-- 詳細リンク --}}
                    <td>
                        @if($attendance)
                            <a href="{{ route('attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
@endsection