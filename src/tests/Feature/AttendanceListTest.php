<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;



class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分の勤怠情報がすべて表示されていることを確認 (ID 9-1)
     */
    public function test_user_can_see_own_attendance_data()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        // テストデータを2日分作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('m/d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->subDay()->format('m/d'),
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('08:30');
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される (ID 9-2)
     */
    public function test_current_month_displayed_by_default()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $currentMonth = now()->format('Y/m'); // UIに合わせて Y/m など調整

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertSee($currentMonth);
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される (ID 9-3)
     */
    public function test_previous_month_navigation()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $prevMonth = now()->subMonth()->format('Y/m');

        // 1. 一覧ページを開く
        // 2. 「前月」ボタンのリンク先へアクセス（クエリパラメータ等を想定）
        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => now()->subMonth()->format('m')]));

        $response->assertSee($prevMonth);
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される (ID 9-4)
     */
    public function test_next_month_navigation()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $nextMonth = now()->addMonth()->format('Y/m');

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => now()->addMonth()->format('m')]));

        $response->assertSee($nextMonth);
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する (ID 9-5)
     */
    public function test_navigate_to_attendance_detail()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('m/d'),
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));
        
        // 詳細画面へのリンク（route('attendance.detail', $id)）が含まれているか
        $detailUrl = route('attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);

        // 実際にアクセスして遷移できるか確認
        $response = $this->get($detailUrl);
        $response->assertStatus(200);
    }

    /**
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている (ID 10-1)
     */
    public function test_detail_page_shows_correct_user_name()
    {
        $user = User::factory()->create(['name' => '山田 太郎', 'email_verified_at' => now()]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('山田 太郎');
    }

    /**
     * 勤怠詳細画面の「日付」が選択した日付になっている (ID 10-2)
     */
    public function test_detail_page_shows_correct_date()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

        // 画面のフォーマットに合わせて '2026年4月1日' や '2026-04-01' に調整してください
        $response->assertSee('2026年'); 
        $response->assertSee('4月1日');
    }

    /**
     * 「出勤・退勤」にて記されている時間が打刻と一致している (ID 10-3)
     */
    public function test_detail_page_shows_correct_times()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 「休憩」にて記されている時間が打刻と一致している (ID 10-4)
     */
    public function test_detail_page_shows_correct_rest_times()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
        ]);
        // 休憩データを作成
        $attendance->restTimes()->create([
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーが発生する (ID 11-1)
     */
    public function test_error_when_clock_in_is_after_clock_out()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 退勤(18:00)より後の出勤(20:00)を送信
        $response = $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '20:00',
            'end_time' => '18:00',
            'remarks' => '修正テスト',
        ]);

        $response->assertSessionHasErrors(['start_time' => '出勤時間が不適切な値です']);
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合 (ID 11-2)
     */
    public function test_error_when_break_start_is_after_clock_out()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            // 送信形式を rest_times に合わせる
            'rest_times' => [
                [
                    'start_time' => '19:00', // 退勤(18:00)より後
                    'end_time' => '20:00',
                ]
            ],
            'remarks' => '修正テスト',
        ]);

        // dd() で確認したキーと、あなたが設定したバリデーション文言を合わせる
        $response->assertSessionHasErrors(['rest_times.0.start_time' => '休憩時間が不適切な値です']);
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合 (ID 11-3)
     */
    public function test_error_when_break_end_is_after_clock_out()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rest_times' => [
                [
                    'start_time' => '12:00',
                    'end_time' => '19:00', // 退勤(18:00)より後
                ]
            ],
            'remarks' => '修正テスト',
        ]);

        $response->assertSessionHasErrors(['rest_times.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される (ID 11-4)
     */
    public function test_error_when_remarks_is_empty()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks' => '', // 未入力
        ]);

        $response->assertSessionHasErrors(['remarks' => '備考を記入してください']);
    }

    /**
     * 修正申請処理が実行され、管理者の申請一覧に表示される (ID 11-5)
     */
    public function test_correction_request_shows_on_admin_list()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]); // 管理者
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
        ]);

        // 1. ユーザーが修正申請を送る
        $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'rest_times' => [
                [
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                ]
            ],
            'remarks' => '申請テスト',
        ]);


        // 2. 管理者でログインして申請一覧を確認
        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.list'));

        $response->assertSee($user->name);
        $response->assertSee('2026/04/01'); // フォーマットは画面に合わせて調整
    }

    /**
     * ユーザーの申請一覧に「承認待ち」の自分の申請が表示される (ID 11-6)
     */
    public function test_user_can_see_own_pending_requests()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        // 直接DBに申請データ（承認待ちステータス）を作成してもOK
        // ここではPOSTして作成する
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-04-02', 'start_time' => '09:00']);
        $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rest_times' => [
                [
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                ]
            ],
            'remarks' => '承認待ちテスト',
        ]);

        $response = $this->get(route('stamp_correction_request.list'));
        $response->assertSee('承認待ち');
        $response->assertSee('承認待ちテスト');
    }

    /**
     * 管理者が承認した申請が「承認済み」として表示される (ID 11-7)
     */
    public function test_user_can_see_approved_requests()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-04-03', 'start_time' => '09:00']);

        // 1. ステータスを「1（HTMLのリンクと一致）」にして作成
        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 1, // 承認済みのコードに合わせる
            'remarks' => '承認済みテスト',
            // もしDBで他の項目が必須なら、ここに追加してください
        ]);

        // 2. URLにステータス「1」を指定してアクセス
        $response = $this->actingAs($user)->get(route('stamp_correction_request.list', ['status' => 1]));

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認済みテスト');
    }

    /**
     * 申請一覧の「詳細」から勤怠詳細画面に遷移する (ID 11-8)
     */
    public function test_navigate_to_detail_from_request_list()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-04-04', 'start_time' => '09:00']);
        
        // 申請を作成
        $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rest_times' => [
                [
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                ]
            ],
            'remarks' => '詳細遷移テスト',
        ]);

        $response = $this->get(route('stamp_correction_request.list'));
        
        // 詳細画面（ID 10で使ったURL）へのリンクがあるか
        $detailUrl = route('attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);
    }
}