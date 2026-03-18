<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StaffController extends Controller
{
    /**
     * PG10: スタッフ一覧
     */
    public function list()
    {
        // 一般ユーザー（role = 0）のみ取得
        $staffs = User::where('role', 0)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.staff.list', compact('staffs'));
    }

    /**
     * PG11: スタッフ別勤怠一覧（月次）
     */
    public function attendance(Request $request, $id)
    {
        $staff = User::where('id', $id)
            ->where('role', 0)
            ->firstOrFail();
        
        // 表示する月を取得（デフォルトは今月）
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        // 月初・月末
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // 勤怠データ取得
        $attendances = Attendance::with('restTimes')
            ->where('user_id', $staff->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();
        
        return view('admin.attendance.staff', compact('staff', 'attendances', 'year', 'month'));
    }

    /**
     * CSV出力（応用機能）
     */
    public function exportCsv(Request $request, $id)
    {
        $staff = User::where('id', $id)
            ->where('role', 0)
            ->firstOrFail();
        
        // 表示する月を取得
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        // 月初・月末
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // 勤怠データ取得
        $attendances = Attendance::with('restTimes')
            ->where('user_id', $staff->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();
        
        // CSVヘッダー
        $csvData = [];
        $csvData[] = ['日付', '出勤時刻', '退勤時刻', '休憩時間', '勤務時間'];
        
        foreach ($attendances as $attendance) {
            // 休憩時間の合計を計算
            $totalBreakMinutes = 0;
            foreach ($attendance->restTimes as $rest) {
                if ($rest->start_time && $rest->end_time) {
                    $start = Carbon::parse($rest->start_time);
                    $end = Carbon::parse($rest->end_time);
                    $totalBreakMinutes += $start->diffInMinutes($end);
                }
            }
            
            // 勤務時間の計算
            $workMinutes = 0;
            if ($attendance->start_time && $attendance->end_time) {
                $start = Carbon::parse($attendance->start_time);
                $end = Carbon::parse($attendance->end_time);
                $workMinutes = $start->diffInMinutes($end) - $totalBreakMinutes;
            }
            
            $csvData[] = [
                $attendance->date,
                $attendance->start_time ?? '',
                $attendance->end_time ?? '',
                $this->formatMinutes($totalBreakMinutes),
                $this->formatMinutes($workMinutes),
            ];
        }
        
        // CSV出力
        $filename = sprintf('%s_%04d%02d_勤怠.csv', $staff->name, $year, $month);
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            
            // BOM追加（Excel対応）
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * 分を「○時間○分」形式に変換
     */
    private function formatMinutes($minutes)
    {
        if ($minutes == 0) {
            return '0分';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0 && $mins > 0) {
            return "{$hours}時間{$mins}分";
        } elseif ($hours > 0) {
            return "{$hours}時間";
        } else {
            return "{$mins}分";
        }
    }
}