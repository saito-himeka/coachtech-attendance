@extends('layouts.app')

@section('title', '勤怠詳細 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@stop


@section('content')
<div class="detail-container">
    <h2 class="page-title">勤怠詳細</h2>

    <form action="#" method="POST" class="detail-form">
        @csrf
        <div class="detail-card">
            {{-- 名前 --}}
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value">
                    <span class="user-name">西 怜奈</span>
                </div>
            </div>

            {{-- 日付 --}}
            <div class="detail-row">
                <div class="detail-label">日付</div>
                <div class="detail-value">
                    <span class="date-text"><strong>2023年</strong></span>
                    <span class="date-text"><strong>6月1日</strong></span>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="detail-row">
                <div class="detail-label">出勤・退勤</div>
                <div class="detail-value inline-group">
                    <input type="text" class="time-input" value="09:00">
                    <span class="separator">～</span>
                    <input type="text" class="time-input" value="20:00">
                </div>
            </div>

            {{-- 休憩 --}}
            <div class="detail-row">
                <div class="detail-label">休憩</div>
                <div class="detail-value inline-group">
                    <input type="text" class="time-input" value="12:00">
                    <span class="separator">～</span>
                    <input type="text" class="time-input" value="13:00">
                </div>
            </div>

            {{-- 休憩2 --}}
            <div class="detail-row">
                <div class="detail-label">休憩2</div>
                <div class="detail-value inline-group">
                    <input type="text" class="time-input" value="">
                    <span class="separator">～</span>
                    <input type="text" class="time-input" value="">
                </div>
            </div>

            {{-- 備考 --}}
            <div class="detail-row no-border">
                <div class="detail-label">備考</div>
                <div class="detail-value">
                    <textarea class="remarks-area"></textarea>
                </div>
            </div>
        </div>

        <div class="form-action">
            <button type="submit" class="update-btn">修正</button>
        </div>
    </form>
</div>
@endsection