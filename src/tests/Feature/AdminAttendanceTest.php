<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;



class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;


    /**
     * その日になされた全ユーザーの勤怠情報が正確に確認できる (ID 12-1)
     */
    public function test_admin_can_see_all_users_attendance_on_specific_day()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user1 = User::factory()->create(['name' => 'ユーザーA']);
        $user2 = User::factory()->create(['name' => 'ユーザーB']);
        
        $date = now()->format('Y-m-d');
        
        // ユーザー1と2の当日の勤怠データを作成
        Attendance::create(['user_id' => $user1->id, 'date' => $date, 'start_time' => '09:00:00']);
        Attendance::create(['user_id' => $user2->id, 'date' => $date, 'start_time' => '10:00:00']);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('ユーザーB');
    }

    /**
     * 勤怠一覧画面に現在の日付が表示される (ID 12-2)
     */
    public function test_admin_attendance_list_shows_current_date()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $today = now()->format('Y/m/d'); // 画面のフォーマットに合わせて調整

        $response = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $response->assertSee($today);
    }

    /**
     * 「前日」を押下した時に前の日の勤怠情報が表示される (ID 12-3)
     */
    public function test_admin_navigation_to_previous_day()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $yesterday = now()->subDay();
        
        // 前日のURL（例: ?date=2026-04-11）を取得
        $prevDateUrl = route('admin.attendance.list', ['date' => $yesterday->format('Y/m/d')]);

        $response = $this->actingAs($admin)->get($prevDateUrl);

        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y/m/d'));
    }

    /**
     * 「翌日」を押下した時に次の日の勤怠情報が表示される (ID 12-4)
     */
    public function test_admin_navigation_to_next_day()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $tomorrow = now()->addDay();
        
        $nextDateUrl = route('admin.attendance.list', ['date' => $tomorrow->format('Y/m/d')]);

        $response = $this->actingAs($admin)->get($nextDateUrl);

        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y/m/d'));
    }

    /**
     * 勤怠詳細画面に表示されるデータが選択したものになっている (ID 13-1)
     */
    public function test_admin_can_see_correct_attendance_detail()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 管理者として詳細画面へアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('2026');
        $response->assertSee('09:00');
    }


    /**
     * 出勤時間が退勤時間より後になっている場合のエラー (ID 13-2)
     */
    public function test_admin_error_when_clock_in_after_clock_out()
    {
        // 1. まず管理者と一般ユーザーを両方作る
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create(); // これでユーザーがDBに存在する状態になる

        // 2. 作成した $user の ID を使って勤怠データを作る
        $attendance = Attendance::create([
            'user_id' => $user->id, // ここを 1 から $user->id に変更
            'date' => '2026-04-01', 
            'start_time' => '09:00'
        ]);

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'remarks' => '修正',
        ]);

        $response->assertSessionHasErrors(['start_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合 (ID 13-3)
     */
    public function test_admin_error_when_break_start_after_clock_out()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create(); 

        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-04-01', 'start_time' => '09:00']);

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rest_times' => [
                ['start_time' => '19:00', 'end_time' => '20:00'] // 退勤より後
            ],
            'remarks' => '修正',
        ]);

        $response->assertSessionHasErrors(['rest_times.0.start_time' => '休憩時間が不適切な値です']);
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合 (ID 13-4)
     */
    public function test_admin_error_when_break_end_after_clock_out()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create();

        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-04-01', 'start_time' => '09:00']);

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rest_times' => [
                ['start_time' => '12:00', 'end_time' => '19:00'] // 退勤より後
            ],
            'remarks' => '修正',
        ]);

        $response->assertSessionHasErrors(['rest_times.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * 備考欄が未入力の場合 (ID 13-5)
     */
    public function test_admin_error_when_remarks_empty()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create();

        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-04-01', 'start_time' => '09:00']);

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks' => '', // 未入力
        ]);

        $response->assertSessionHasErrors(['remarks' => '備考を記入してください']);
    }

    /**
     * 管理者が全一般ユーザーの「氏名」「メールアドレス」を確認できる (ID 14-1)
     */
    public function test_admin_can_see_staff_list()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create(['name' => 'テスト太郎', 'email' => 'test@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('test@example.com');
    }

    /**
     * 選択したユーザーの勤怠情報が正しく表示される (ID 14-2)
     */
    public function test_admin_can_see_specific_staff_attendance()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create(['name' => 'テスト太郎']);
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00'
        ]);

        // 管理者用のスタッフ勤怠URLへアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', ['id' => $user->id]));

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される (ID 14-3)
     */
    public function test_admin_can_navigate_staff_attendance_to_previous_month()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create();
        
        // 前月の「年」と「月」を別々に取得する
        $lastMonthDate = now()->subMonth();
        $year = $lastMonthDate->year;
        $month = $lastMonthDate->month;

        // パラメータの渡し方を調整（コントローラーが期待している形に合わせる）
        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'year' => $year,  // もしコントローラーが year/month で受けているなら
            'month' => $month, // '2026/03' ではなく '3' (数値) を渡す
        ]));

        $response->assertStatus(200);
        $response->assertSee($year);
        $response->assertSee($month);
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される (ID 14-4)
     */
    public function test_admin_can_navigate_staff_attendance_to_next_month()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create();
        
        // 翌月の「年」と「月」を個別に取得
        $nextMonthDate = now()->addMonth();
        $year = $nextMonthDate->year;
        $month = $nextMonthDate->month;

        // パラメータを数値で渡す
        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'year' => $year,
            'month' => $month, // ここを文字列 '2026-05' ではなく数値の 5 にする
        ]));

        $response->assertStatus(200);
        $response->assertSee($year);
        $response->assertSee($month);
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する (ID 14-5)
     */
    public function test_admin_can_navigate_to_attendance_detail_from_staff_list()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', ['id' => $user->id]));

        // 詳細画面へのリンクが存在するか確認（リンク先が admin.attendance.detail になっているか）
        $detailUrl = route('admin.attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);
    }

    /**
     * 承認待ちの修正申請が全て表示されている (ID 15-1)
     */
    public function test_admin_can_see_all_pending_requests()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create(['name' => '申請太郎']);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'status' => 0, 
            'remarks' => '打刻忘れのため',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        // 正しいルート名に変更
        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('申請太郎');
        $response->assertSee('打刻忘れのため');
    }

    /**
     * 承認済みの修正申請が全て表示されている (ID 15-2)
     */
    public function test_admin_can_see_all_approved_requests()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create(['name' => '承認済次郎']);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'status' => 1,
            'remarks' => '修正完了分',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        // クエリパラメータ ?status=1 を追加して「承認済み」タブを開かせる
        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.list', ['status' => '1']));

        $response->assertStatus(200);
        $response->assertSee('承認済次郎');
        $response->assertSee('修正完了分');
    }

    /**
     * 修正申請の詳細内容が正しく表示されている (ID 15-3)
     */
    public function test_admin_can_see_request_detail()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'status' => 0,
            'remarks' => '詳細確認テスト',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        // 正しいルート名に変更 (詳細/承認画面)
        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.approve', ['id' => $request->id]));

        $response->assertStatus(200);
        $response->assertSee('詳細確認テスト');
    }

    /**
     * 修正申請の承認処理が正しく行われる (ID 15-4)
     */
    public function test_admin_can_approve_request()
    {
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
        $user = User::factory()->create();
        
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '10:00',
        ]);

        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'status' => 0,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks' => '修正します',
        ]);

        // 正しいルート名に変更 (承認処理)
        $response = $this->actingAs($admin)->post(route('admin.stamp_correction_request.process', ['id' => $request->id]));

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => 1
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '09:00:00'
        ]);
    }
}