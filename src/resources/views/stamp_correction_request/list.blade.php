@extends('layouts.app')

@section('title', '申請一覧 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/list.css') }}">
@stop


@section('content')
<div class="request-list-container">
    <h2 class="page-title">申請一覧</h2>

    {{-- タブメニュー --}}
    <div class="tab-menu">
        @if(auth()->user()->role == 1)
            {{-- 管理者用タブ --}}
            <a href="{{ route('admin.stamp_correction_request.list', ['status' => 0]) }}" 
               class="tab-item {{ request('status', 0) == 0 ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('admin.stamp_correction_request.list', ['status' => 1]) }}" 
               class="tab-item {{ request('status') == 1 ? 'active' : '' }}">承認済み</a>
        @else
            {{-- 一般ユーザー用タブ --}}
            <a href="{{ route('stamp_correction_request.list', ['status' => 0]) }}" 
               class="tab-item {{ request('status', 0) == 0 ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('stamp_correction_request.list', ['status' => 1]) }}" 
               class="tab-item {{ request('status') == 1 ? 'active' : '' }}">承認済み</a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-wrapper">
        @if($requests->isEmpty())
            <div class="no-data">
                <p>申請データがありません</p>
            </div>
        @else
            <table class="request-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr>
                            {{-- 状態 --}}
                            <td>
                                @if($request->status == 0)
                                    承認待ち
                                @elseif($request->status == 1)
                                    承認済み
                                @endif
                            </td>
                            
                            {{-- 名前 --}}
                            <td>{{ $request->attendance->user->name }}</td>
                            
                            {{-- 対象日時 --}}
                            <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</td>
                            
                            {{-- 申請理由 --}}
                            <td class="remarks-cell">{{ $request->remarks }}</td>
                            
                            {{-- 申請日時 --}}
                            <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                            
                            {{-- 詳細 --}}
                            <td>
                                @if(auth()->user()->role == 1)
                                    <a href="{{ route('admin.stamp_correction_request.approve', $request->id) }}" class="detail-link">詳細</a>
                                @else
                                    <a href="{{ route('attendance.detail', $request->attendance_id) }}" class="detail-link">詳細</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection