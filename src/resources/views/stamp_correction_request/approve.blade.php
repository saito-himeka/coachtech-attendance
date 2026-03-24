@extends('layouts.app')

@section('title', '修正申請承認 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/approve.css') }}">
@stop


@section('content')
<div class="request-detail-container">
    <h2 class="page-title">勤怠詳細</h2>

    <form action="{{ route('admin.stamp_correction_request.process', $correctionRequest->id) }}" method="POST" class="approval-form">
        @csrf
        
        <div class="detail-card">
            {{-- 名前 --}}
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value text-only">
                    {{ $correctionRequest->attendance->user->name }}
                </div>
            </div>

            {{-- 日付 --}}
            <div class="detail-row">
                <div class="detail-label">日付</div>
                <div class="detail-value text-only">
                    @php
                        $date = \Carbon\Carbon::parse($correctionRequest->attendance->date);
                    @endphp
                    <span class="date-text">{{ $date->format('Y年') }}</span>
                    <span class="date-text">{{ $date->format('n月j日') }}</span>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="detail-row">
                <div class="detail-label">出勤・退勤</div>
                <div class="detail-value text-only">
                    <span class="time-display">
                        {{ $correctionRequest->start_time ? \Carbon\Carbon::parse($correctionRequest->start_time)->format('H:i') : '' }}
                    </span>
                    <span class="separator">～</span>
                    <span class="time-display">
                        {{ $correctionRequest->end_time ? \Carbon\Carbon::parse($correctionRequest->end_time)->format('H:i') : '' }}
                    </span>
                </div>
            </div>

            {{-- 休憩 --}}
            @php
                $rest1 = $correctionRequest->rest_times[0] ?? null;
            @endphp
            <div class="detail-row">
                <div class="detail-label">休憩</div>
                <div class="detail-value text-only">
                    @if($rest1)
                        <span class="time-display">{{ $rest1['start_time'] ?? '' }}</span>
                        <span class="separator">～</span>
                        <span class="time-display">{{ $rest1['end_time'] ?? '' }}</span>
                    @endif
                </div>
            </div>

            {{-- 休憩2 --}}
            @php
                $rest2 = $correctionRequest->rest_times[1] ?? null;
            @endphp
            <div class="detail-row">
                <div class="detail-label">休憩2</div>
                <div class="detail-value text-only">
                    @if($rest2)
                        <span class="time-display">{{ $rest2['start_time'] ?? '' }}</span>
                        <span class="separator">～</span>
                        <span class="time-display">{{ $rest2['end_time'] ?? '' }}</span>
                    @endif
                </div>
            </div>

            {{-- 備考 --}}
            <div class="detail-row no-border">
                <div class="detail-label">備考</div>
                <div class="detail-value text-only remarks-text">
                    {{ $correctionRequest->remarks }}
                </div>
            </div>
        </div>

        <div class="form-action">
            <button type="submit" class="approve-btn" 
                    @if($correctionRequest->status == 1) disabled @endif>
                {{ $correctionRequest->status == 1 ? '承認済み' : '承認' }}
            </button>
        </div>
    </form>
</div>
@endsection