@extends('layouts.app')

@section('title', 'スタッフ勤怠 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css') }}">
@stop

@section('content')
<div class="staff-attendance-container">
    <h2 class="page-title">{{ $staff->name }}さんの勤怠</h2>

    <div class="month-pager">
        @php
            $prevYear = $month == 1 ? $year - 1 : $year;
            $prevMonth = $month == 1 ? 12 : $month - 1;
            $nextYear = $month == 12 ? $year + 1 : $year;
            $nextMonth = $month == 12 ? 1 : $month + 1;
        @endphp
        
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'year' => $prevYear, 'month' => $prevMonth]) }}" class="pager-btn">← 前月</a>
        <span class="current-month">
            <span class="calendar-icon">📅</span> {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
        </span>
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'year' => $nextYear, 'month' => $nextMonth]) }}" class="pager-btn">翌月 →</a>
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
            @php
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
                
                // 勤怠データを日付でインデックス化
                $attendanceByDate = [];
                foreach($attendances as $attendance) {
                    $dateKey = $attendance->date instanceof \Carbon\Carbon 
                        ? $attendance->date->format('Y-m-d') 
                        : $attendance->date;
                    $attendanceByDate[$dateKey] = $attendance;
                }
            @endphp
            
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = \Carbon\Carbon::createFromDate($year, $month, $day);
                    $dateString = $date->format('Y-m-d');
                    $weekday = $weekdays[$date->dayOfWeek];
                    
                    // その日の勤怠データを取得
                    $attendance = $attendanceByDate[$dateString] ?? null;
                    
                    // 休憩時間を計算
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
                    
                    {{-- 出勤 --}}
                    <td>
                        @if($attendance && $attendance->start_time)
                            {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}
                        @endif
                    </td>
                    
                    {{-- 退勤 --}}
                    <td>
                        @if($attendance && $attendance->end_time)
                            {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
                        @endif
                    </td>
                    
                    {{-- 休憩 --}}
                    <td>
                        @if($totalBreakMinutes > 0)
                            @php
                                $hours = floor($totalBreakMinutes / 60);
                                $minutes = $totalBreakMinutes % 60;
                            @endphp
                            {{ $hours }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>
                    
                    {{-- 合計 --}}
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
                    
                    {{-- 詳細 --}}
                    <td>
                        @if($attendance)
                            <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="csv-action">
        <form action="{{ route('admin.attendance.staff.csv', $staff->id) }}" method="GET" style="display: inline;">
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <button type="submit" class="csv-btn">CSV出力</button>
        </form>
    </div>
</div>
@endsection