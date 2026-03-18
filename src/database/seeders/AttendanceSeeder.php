<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RestTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 一般ユーザーを取得
        $users = User::where('role', 0)->get();

        foreach ($users as $user) {
            // 過去30日分の勤怠データを作成
            for ($i = 1; $i <= 30; $i++) {
                $date = Carbon::now()->subDays($i);

                // 土日はスキップ
                if ($date->isWeekend()) {
                    continue;
                }

                // 勤怠レコード作成
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                ]);

                // 休憩時間を作成（お昼休憩）
                RestTime::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => '12:00:00',
                    'end_time' => '13:00:00',
                ]);

                // ランダムで午後の休憩も追加（30%の確率）
                if (rand(1, 10) <= 3) {
                    RestTime::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => '15:00:00',
                        'end_time' => '15:15:00',
                    ]);
                }
            }
        }

        // 今日の勤怠データ（打刻テスト用）
        if ($users->isNotEmpty()) {
            $testUser = $users->first();
            $today = Carbon::today();

            // 既に今日のデータがあれば削除
            Attendance::where('user_id', $testUser->id)
                ->where('date', $today)
                ->delete();

            // 今日の勤怠（出勤のみ、まだ退勤していない）
            $todayAttendance = Attendance::create([
                'user_id' => $testUser->id,
                'date' => $today->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => null, // まだ退勤していない
            ]);

            // 午前の休憩（完了済み）
            RestTime::create([
                'attendance_id' => $todayAttendance->id,
                'start_time' => '10:30:00',
                'end_time' => '10:45:00',
            ]);
        }
    }
}