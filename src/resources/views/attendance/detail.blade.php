@extends('layouts.app')

@section('title', '勤怠詳細 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@stop

@section('content')
<div class="attendance-detail-container">
    <h2 class="page-title">勤怠詳細</h2>

    @php
        // 承認待ちの申請を取得
        $pendingRequest = $attendance->stampCorrectionRequests()
            ->where('status', 0)
            ->first();
        
        // 承認済みの申請を取得
        $approvedRequest = $attendance->stampCorrectionRequests()
            ->where('status', 1)
            ->first();
        
        $hasPendingRequest = !is_null($pendingRequest);
        $hasApprovedRequest = !is_null($approvedRequest);
    @endphp

    {{-- 成功メッセージ --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('stamp_correction_request.store') }}" method="POST" class="attendance-form" novalidate>
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        
        <div class="detail-card">
            {{-- 名前（表示のみ） --}}
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value readonly">
                    <span class="date-name">{{ auth()->user()->name }}</span>
                </div>
            </div>

            {{-- 日付（表示のみ） --}}
            <div class="detail-row">
                <div class="detail-label">日付</div>
                <div class="detail-value readonly">
                    @php
                        $date = \Carbon\Carbon::parse($attendance->date);
                    @endphp
                    <span class="date-year">{{ $date->format('Y年') }}</span>
                    <span class="date-day">{{ $date->format('n月j日') }}</span>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="detail-row">
                <div class="detail-label">出勤・退勤</div>
                <div class="detail-value-wrapper">
                    @if($hasPendingRequest)
                        {{-- 承認待ち：申請内容を表示 --}}
                        <div class="detail-value text-only">
                            <span class="time-display">{{ $pendingRequest->start_time ? \Carbon\Carbon::parse($pendingRequest->start_time)->format('H:i') : '' }}</span>
                            <span class="separator">～</span>
                            <span class="time-display">{{ $pendingRequest->end_time ? \Carbon\Carbon::parse($pendingRequest->end_time)->format('H:i') : '' }}</span>
                        </div>
                    @elseif($hasApprovedRequest)
                        {{-- 承認済み：承認された内容を表示（= 現在の勤怠データ） --}}
                        <div class="detail-value text-only">
                            <span class="time-display">{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}</span>
                            <span class="separator">～</span>
                            <span class="time-display">{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}</span>
                        </div>
                    @else
                        {{-- 申請なし：入力フィールド --}}
                        <div class="detail-value">
                            <input type="text" name="start_time" class="time-input @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time', $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                            <span class="separator">～</span>
                            <input type="text" name="end_time" class="time-input @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                        </div>
                        @error('start_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('end_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            {{-- 休憩1 --}}
            @php
                $rest1 = $attendance->restTimes->get(0);
                $pendingRest1 = $hasPendingRequest && isset($pendingRequest->rest_times[0]) ? $pendingRequest->rest_times[0] : null;
            @endphp
            <div class="detail-row">
                <div class="detail-label">休憩</div>
                <div class="detail-value-wrapper">
                    @if($hasPendingRequest)
                        {{-- 承認待ち：申請内容を表示 --}}
                        <div class="detail-value text-only">
                            <span class="time-display">{{ $pendingRest1['start_time'] ?? '' }}</span>
                            <span class="separator">～</span>
                            <span class="time-display">{{ $pendingRest1['end_time'] ?? '' }}</span>
                        </div>
                    @elseif($hasApprovedRequest)
                        {{-- 承認済み：承認された内容を表示 --}}
                        <div class="detail-value text-only">
                            <span class="time-display">{{ $rest1 && $rest1->start_time ? \Carbon\Carbon::parse($rest1->start_time)->format('H:i') : '' }}</span>
                            <span class="separator">～</span>
                            <span class="time-display">{{ $rest1 && $rest1->end_time ? \Carbon\Carbon::parse($rest1->end_time)->format('H:i') : '' }}</span>
                        </div>
                    @else
                        {{-- 申請なし：入力フィールド --}}
                        <div class="detail-value">
                            <input type="text" name="rest_times[0][start_time]" class="time-input @error('rest_times.0.start_time') is-invalid @enderror" 
                                   value="{{ old('rest_times.0.start_time', $rest1 && $rest1->start_time ? \Carbon\Carbon::parse($rest1->start_time)->format('H:i') : '') }}">
                            <span class="separator">～</span>
                            <input type="text" name="rest_times[0][end_time]" class="time-input @error('rest_times.0.end_time') is-invalid @enderror" 
                                   value="{{ old('rest_times.0.end_time', $rest1 && $rest1->end_time ? \Carbon\Carbon::parse($rest1->end_time)->format('H:i') : '') }}">
                        </div>
                        @error('rest_times.0.start_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('rest_times.0.end_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            {{-- 休憩2 --}}
            @php
                $rest2 = $attendance->restTimes->get(1);
                $pendingRest2 = $hasPendingRequest && isset($pendingRequest->rest_times[1]) ? $pendingRequest->rest_times[1] : null;
            @endphp
            <div class="detail-row">
                <div class="detail-label">休憩2</div>
                <div class="detail-value-wrapper">
                    @if($hasPendingRequest)
                        {{-- 承認待ち：申請内容を表示 --}}
                        <div class="detail-value text-only">
                            @if($pendingRest2)
                                <span class="time-display">{{ $pendingRest2['start_time'] ?? '' }}</span>
                                <span class="separator">～</span>
                                <span class="time-display">{{ $pendingRest2['end_time'] ?? '' }}</span>
                            @endif
                        </div>
                    @elseif($hasApprovedRequest)
                        {{-- 承認済み：承認された内容を表示 --}}
                        <div class="detail-value text-only">
                            @if($rest2)
                                <span class="time-display">{{ $rest2 && $rest2->start_time ? \Carbon\Carbon::parse($rest2->start_time)->format('H:i') : '' }}</span>
                                <span class="separator">～</span>
                                <span class="time-display">{{ $rest2 && $rest2->end_time ? \Carbon\Carbon::parse($rest2->end_time)->format('H:i') : '' }}</span>
                            @endif
                        </div>
                    @else
                        {{-- 申請なし：入力フィールド --}}
                        <div class="detail-value">
                            <input type="text" name="rest_times[1][start_time]" class="time-input @error('rest_times.1.start_time') is-invalid @enderror" 
                                   value="{{ old('rest_times.1.start_time', $rest2 && $rest2->start_time ? \Carbon\Carbon::parse($rest2->start_time)->format('H:i') : '') }}">
                            <span class="separator">～</span>
                            <input type="text" name="rest_times[1][end_time]" class="time-input @error('rest_times.1.end_time') is-invalid @enderror" 
                                   value="{{ old('rest_times.1.end_time', $rest2 && $rest2->end_time ? \Carbon\Carbon::parse($rest2->end_time)->format('H:i') : '') }}">
                        </div>
                        @error('rest_times.1.start_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('rest_times.1.end_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            {{-- 備考 --}}
            <div class="detail-row no-border">
                <div class="detail-label">備考</div>
                <div class="detail-value-wrapper">
                    @if($hasPendingRequest)
                        {{-- 承認待ち：申請内容を表示 --}}
                        <div class="detail-value text-only remarks-text">
                            {{ $pendingRequest->remarks }}
                        </div>
                    @elseif($hasApprovedRequest)
                        {{-- 承認済み：承認された備考を表示 --}}
                        <div class="detail-value text-only remarks-text">
                            {{ $approvedRequest->remarks }}
                        </div>
                    @else
                        {{-- 申請なし：テキストエリア --}}
                        <textarea name="remarks" class="reason-textarea @error('remarks') is-invalid @enderror" 
                                placeholder="修正理由を入力してください">{{ old('remarks') }}</textarea>
                        @error('remarks')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>
        </div>

        <div class="form-action">
            @if($hasPendingRequest)
                {{-- 承認待ち --}}
                <p class="pending-message">*承認待ちのため修正はできません。</p>
            @elseif($hasApprovedRequest)
                {{-- 承認済み --}}
                <p class="approved-message">承認済み</p>
            @else
                {{-- 申請なし：修正ボタン表示 --}}
                <button type="submit" class="update-btn">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection