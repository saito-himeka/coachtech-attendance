<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
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
    $user = User::factory()->create(['name' => '斉藤 姫香', 'email_verified_at' => now()]);
    $attendance = Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-04-01',
        'start_time' => '09:00:00',
    ]);

    $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

    $response->assertStatus(200);
    $response->assertSee('斉藤 姫香');
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
    $response->assertSee('2026年4月1日'); 
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
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される (ID 11)
     */
    public function test_user_cannot_update_attendance_with_invalid_time_order()
    {
        $user = User::factory()->create(['role' => 2]); // 一般ユーザー
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 修正申請の保存先へPOST送信（ルート名は web.php に合わせました）
        $response = $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '18:00', // 出勤を遅い時間に設定
            'end_time' => '09:00',   // 退勤を早い時間に設定
            'remarks' => '修正理由のテスト',
        ]);

        // セッションにエラーが含まれているか確認
        $response->assertSessionHasErrors(['start_time']);
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される (ID 11)
     */
    public function test_user_cannot_update_attendance_without_remarks()
    {
        $user = User::factory()->create(['role' => 2]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks' => '', // 備考を空にする [cite: 7]
        ]);

        $response->assertSessionHasErrors(['remarks']);
    }
}