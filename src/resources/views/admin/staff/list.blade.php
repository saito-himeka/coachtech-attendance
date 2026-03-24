@extends('layouts.app')

@section('title', 'スタッフ一覧 - COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">
@stop

@section('content')
<div class="staff-container">
    <h2 class="page-title">スタッフ一覧</h2>

    @if($staffs->isEmpty())
        <div style="background: #fff; padding: 40px; text-align: center; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <p style="color: #999;">スタッフが登録されていません</p>
        </div>
    @else
        <table class="staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>
                        {{-- ✅ 正しいルート名 --}}
                        <a href="{{ route('admin.attendance.staff', $staff->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection