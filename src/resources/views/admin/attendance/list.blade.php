@extends('layouts.app')

@section('title', '勤怠一覧 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@stop

@section('content')
<div class="attendance-container">
    <h2 class="page-title">
        {{ $targetDate->format('Y年n月j日') }}の勤怠
    </h2>

    <div class="date-pager">
        <a href="{{ route('admin.attendance.list', ['date' => $targetDate->copy()->subDay()->format('Y-m-d')]) }}" class="pager-btn">← 前日</a>
        <span class="current-date">
            <span class="calendar-icon">📅</span> {{ $targetDate->format('Y/m/d') }}
        </span>
        <a href="{{ route('admin.attendance.list', ['date' => $targetDate->copy()->addDay()->format('Y-m-d')]) }}" class="pager-btn">翌日 →</a>
    </div>

    @if($attendances->isEmpty())
        <div style="background: #fff; padding: 40px; text-align: center; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <p style="color: #999;">この日の勤怠データはありません</p>
        </div>
    @else
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
                    {{-- 名前 --}}
                    <td>{{ $attendance->user->name }}</td>
                    
                    {{-- 出勤 --}}
                    <td>{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}</td>
                    
                    {{-- 退勤 --}}
                    <td>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}</td>
                    
                    {{-- 休憩時間 --}}
                    <td>
                        @php
                            $totalBreakMinutes = 0;
                            foreach($attendance->restTimes as $rest) {
                                if($rest->start_time && $rest->end_time) {
                                    $start = \Carbon\Carbon::parse($rest->start_time);
                                    $end = \Carbon\Carbon::parse($rest->end_time);
                                    $totalBreakMinutes += $start->diffInMinutes($end);
                                }
                            }
                            $hours = floor($totalBreakMinutes / 60);
                            $minutes = $totalBreakMinutes % 60;
                        @endphp
                        
                        @if($totalBreakMinutes > 0)
                            {{ $hours }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>
                    
                    {{-- 勤務時間（合計） --}}
                    <td>
                        @if($attendance->start_time && $attendance->end_time)
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
                        <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection