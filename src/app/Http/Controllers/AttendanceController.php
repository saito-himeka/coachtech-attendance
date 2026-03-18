<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\RestTime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * PG03: 勤怠登録（打刻画面）
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        // 本日の勤怠記録を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        // ステータス判定
        $status = $this->getStatus($attendance);
        
        return view('attendance.index', compact('attendance', 'status'));
    }

    /**
     * 出勤
     */
    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        // 既に出勤済みかチェック
        $exists = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->exists();
        
        if ($exists) {
            return redirect()->route('attendance.index')
                ->with('error', '既に出勤しています。');
        }
        
        // 出勤記録を作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => Carbon::now()->format('H:i:s'),
        ]);
        
        return redirect()->route('attendance.index')
            ->with('success', '出勤しました。');
    }

    /**
     * 休憩入
     */
    public function breakStart(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        if (!$attendance) {
            return redirect()->route('attendance.index')
                ->with('error', '出勤記録がありません。');
        }
        
        // 既に休憩中かチェック
        $onBreak = RestTime::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->exists();
        
        if ($onBreak) {
            return redirect()->route('attendance.index')
                ->with('error', '既に休憩中です。');
        }
        
        // 休憩開始
        RestTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->format('H:i:s'),
        ]);
        
        return redirect()->route('attendance.index')
            ->with('success', '休憩を開始しました。');
    }

    /**
     * 休憩戻
     */
    public function breakEnd(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        if (!$attendance) {
            return redirect()->route('attendance.index')
                ->with('error', '出勤記録がありません。');
        }
        
        // 休憩中のレコードを取得
        $restTime = RestTime::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->first();
        
        if (!$restTime) {
            return redirect()->route('attendance.index')
                ->with('error', '休憩中ではありません。');
        }
        
        // 休憩終了
        $restTime->update([
            'end_time' => Carbon::now()->format('H:i:s'),
        ]);
        
        return redirect()->route('attendance.index')
            ->with('success', '休憩を終了しました。');
    }

    /**
     * 退勤
     */
    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        if (!$attendance) {
            return redirect()->route('attendance.index')
                ->with('error', '出勤記録がありません。');
        }
        
        if ($attendance->end_time) {
            return redirect()->route('attendance.index')
                ->with('error', '既に退勤しています。');
        }
        
        // 休憩中かチェック
        $onBreak = RestTime::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->exists();
        
        if ($onBreak) {
            return redirect()->route('attendance.index')
                ->with('error', '休憩中です。休憩を終了してから退勤してください。');
        }
        
        // 退勤記録
        $attendance->update([
            'end_time' => Carbon::now()->format('H:i:s'),
        ]);
        
        return redirect()->route('attendance.index')
            ->with('success', 'お疲れ様でした。');
    }

    /**
     * PG04: 勤怠一覧（月次）
     */
    public function list(Request $request)
    {
        $user = auth()->user();
        
        // 表示する月を取得（デフォルトは今月）
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        // 月初・月末
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // 勤怠データ取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();
        
        return view('attendance.list', compact('attendances', 'year', 'month'));
    }

    /**
     * PG05: 勤怠詳細
     */
    public function detail($id)
    {
        $user = auth()->user();
        
        $attendance = Attendance::with('restTimes')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        return view('attendance.detail', compact('attendance'));
    }

    /**
     * ステータス判定
     */
    private function getStatus($attendance)
    {
        if (!$attendance) {
            return 'not_working'; // 勤務外
        }
        
        if ($attendance->end_time) {
            return 'clocked_out'; // 退勤済
        }
        
        // 休憩中かチェック
        $onBreak = RestTime::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->exists();
        
        if ($onBreak) {
            return 'on_break'; // 休憩中
        }
        
        return 'working'; // 出勤中
    }
}