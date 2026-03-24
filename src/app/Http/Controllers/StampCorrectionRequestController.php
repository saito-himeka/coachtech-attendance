<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Http\Requests\StampCorrectionRequestRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    /**
     * PG06: 申請一覧（一般ユーザー）
     */
    public function list(Request $request)
    {
        $user = auth()->user();
        
        // ステータスをクエリパラメータから取得（デフォルト: 0 = 承認待ち）
        $status = $request->input('status', 0);
        
        // 自分の申請一覧を取得
        $requests = StampCorrectionRequest::with(['attendance.user'])
            ->whereHas('attendance', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('stamp_correction_request.list', compact('requests'));
    }

    /**
     * 修正申請の送信処理
     */
    public function store(StampCorrectionRequestRequest $request)
    {
        $user = auth()->user();
        
        // 勤怠データが本人のものか確認
        $attendance = Attendance::where('id', $request->attendance_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // 既に承認待ちの申請があるかチェック
        $existingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 0)
            ->exists();
        
        if ($existingRequest) {
            return redirect()->back()
                ->with('error', '既に承認待ちの申請があります。');
        }
        
        // 休憩時間のデータを整形
        $restTimes = [];
        if ($request->has('rest_times')) {
            foreach ($request->rest_times as $rest) {
                if (!empty($rest['start_time']) || !empty($rest['end_time'])) {
                    $restTimes[] = [
                        'start_time' => $rest['start_time'] ?? null,
                        'end_time' => $rest['end_time'] ?? null,
                    ];
                }
            }
        }
        
        // 修正申請を作成
        StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'rest_times' => $restTimes,
            'remarks' => $request->remarks,
            'status' => 0, // 承認待ち
        ]);
        
        return redirect()->route('attendance.detail', $attendance->id)
            ->with('success', '修正申請を送信しました。');
    }

    /**
     * PG12: 申請一覧（管理者用）
     */
    public function adminList(Request $request)
    {
        // ステータスをクエリパラメータから取得（デフォルト: 0 = 承認待ち）
        $status = $request->input('status', 0);
        
        // 全ユーザーの申請一覧を取得
        $requests = StampCorrectionRequest::with(['attendance.user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('stamp_correction_request.list', compact('requests'));
    }

    /**
     * PG13: 修正申請承認画面
     */
    public function approve($id)
    {
        $correctionRequest = StampCorrectionRequest::with(['attendance.user', 'attendance.restTimes'])
            ->findOrFail($id);
        
        return view('stamp_correction_request.approve', compact('correctionRequest'));
    }

    /**
     * 修正申請の承認処理
     */
    public function processApproval(Request $request, $id)
    {
        $correctionRequest = StampCorrectionRequest::with('attendance')
            ->findOrFail($id);
        
        if ($correctionRequest->status == 1) {
            return redirect()->back()
                ->with('error', '既に承認済みです。');
        }
        
        \DB::beginTransaction();
        
        try {
            $attendance = $correctionRequest->attendance;
            
            $attendance->update([
                'start_time' => $correctionRequest->start_time,
                'end_time' => $correctionRequest->end_time,
            ]);
            
            $attendance->restTimes()->delete();
            
            if (!empty($correctionRequest->rest_times)) {
                foreach ($correctionRequest->rest_times as $rest) {
                    if (!empty($rest['start_time']) && !empty($rest['end_time'])) {
                        $attendance->restTimes()->create([
                            'start_time' => $rest['start_time'],
                            'end_time' => $rest['end_time'],
                        ]);
                    }
                }
            }
            
            $correctionRequest->update([
                'status' => 1, // 承認済み
            ]);
            
            \DB::commit();
            
            return redirect()->route('admin.stamp_correction_request.list')
                ->with('success', '修正申請を承認しました。');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return redirect()->back()
                ->with('error', '承認処理に失敗しました。');
        }
    }
}