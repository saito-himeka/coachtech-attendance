<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RestTime;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    /**
     * PG08: 日次勤怠一覧（管理者）
     */
    public function list(Request $request)
    {
        // 表示する日付を取得（デフォルトは今日）
        $date = $request->input('date', Carbon::today()->toDateString());
        $targetDate = Carbon::parse($date);
        
        // その日の全ユーザーの勤怠データを取得
        $attendances = Attendance::with('user', 'restTimes')
            ->where('date', $targetDate)
            ->orderBy('user_id', 'asc')
            ->get();
        
        return view('admin.attendance.list', compact('attendances', 'targetDate'));
    }

    /**
     * PG09: 勤怠詳細（管理者）
     */
    public function detail($id)
    {
        $attendance = Attendance::with('user', 'restTimes', 'stampCorrectionRequests')
            ->findOrFail($id);
        
        // 承認待ちの申請があるかチェック
        $hasPendingRequest = $attendance->stampCorrectionRequests()
            ->where('status', 0)
            ->exists();
        
        return view('admin.attendance.detail', compact('attendance', 'hasPendingRequest'));
    }

    /**
     * 管理者による直接修正
     */
    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('restTimes')
            ->findOrFail($id);
        
        // 承認待ちの申請があるかチェック
        $hasPendingRequest = $attendance->stampCorrectionRequests()
            ->where('status', 0)
            ->exists();
        
        if ($hasPendingRequest) {
            return redirect()->back()
                ->with('error', '承認待ちのため修正はできません。');
        }
        
        // トランザクション開始
        \DB::beginTransaction();
        
        try {
            // 勤怠データを更新
            $attendance->update([
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
            ]);
            
            // 既存の休憩時間を削除
            RestTime::where('attendance_id', $attendance->id)->delete();
            
            // 新しい休憩時間を作成
            $restTimes = $request->input('rest_times', []);
            
            foreach ($restTimes as $rest) {
                if (!empty($rest['start_time'])) {
                    RestTime::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $rest['start_time'],
                        'end_time' => $rest['end_time'] ?? null,
                    ]);
                }
            }
            
            \DB::commit();
            
            return redirect()->back()
                ->with('success', '勤怠情報を修正しました。');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return redirect()->back()
                ->with('error', '修正処理に失敗しました。');
        }
    }
}