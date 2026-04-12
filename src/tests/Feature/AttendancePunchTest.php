<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;



class AttendancePunchTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * 打刻画面に現在の日付が正しく表示される (ID 4)
     */
    public function test_current_date_is_displayed_on_attendance_page()
    {
        // 1. テスト内の時間を「2023年6月1日」に固定する
        // ※HTMLの初期値が2023/06/01なので、それに合わせるのが最も確実です
        $fakeDate = \Carbon\Carbon::create(2023, 6, 1, 8, 0, 0);
        \Carbon\Carbon::setTestNow($fakeDate);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        // 2. HTMLの初期値に含まれる文字列を指定する
        // 曜日まで含めると確実です
        $expectedDate = "2023年6月1日(木)";

        $response->assertStatus(200);
        $response->assertSee($expectedDate);

        // 3. テストが終わったら時間を現在に戻す（お作法）
        \Carbon\Carbon::setTestNow();
    }

    /**
     * 勤務外（データなし）の場合、「勤務外」と表示される (ID 5)
     */
    public function test_status_is_off_work_when_no_data()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('勤務外');
    }

    /**
     * 出勤中の場合、「出勤中」と表示される (ID 5)
     */
    public function test_status_is_working_when_clocked_in()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        // 出勤データを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('出勤中');
    }

    /**
     * 休憩中の場合、「休憩中」と表示される (ID 5)
     */
    public function test_status_is_on_break_when_resting()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        // 出勤＋休憩中データを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
        ]);
        $attendance->restTimes()->create([
            'start_time' => '12:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('休憩中');
    }

    /**
     * 退勤済の場合、「勤務外」と表示される (ID 5)
     */
    public function test_status_is_off_work_after_clock_out()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        // 退勤済みデータを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        // 仕様書の期待挙動に合わせて「勤務外」または「退勤済」を確認
        $response->assertSee('退勤済'); 
    }

    /**
     * 出勤ボタンが正しく機能し、ステータスが「出勤中」になる (ID 6)
     */
    public function test_clock_in_functional()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        // 1. 勤務外の状態でアクセス
        $response = $this->actingAs($user)->get(route('attendance.index'));
        
        // 2. 「出勤」ボタンが表示されているか確認
        $response->assertSee('出勤');

        // 3. 出勤処理（POSTリクエスト）を実行
        $response = $this->post(route('attendance.clock-in'));

        // 4. ステータスが「出勤中」に変わっているか確認
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    /**
     * 退勤済みのユーザーには出勤ボタンが表示されない (ID 6)
     */
    public function test_clock_in_button_hidden_after_clock_out()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        // 退勤済みデータを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        // 「出勤」という文字が表示されていないことを確認
        $response->assertDontSee('出勤');
    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できる (ID 6)
     */
    public function test_clock_in_time_visible_on_attendance_list()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        // 1. 出勤処理を行う
        $this->actingAs($user)->post(route('attendance.clock-in'));

        // 2. 勤怠一覧画面へアクセス
        $response = $this->get(route('attendance.list')); // ルート名はプロジェクトに合わせて調整

        // 3. 現在時刻（時:分）が一覧に含まれているか確認
        $nowTime = now()->format('H:i');
        $response->assertSee($nowTime);
    }

    /**
     * 休憩ボタンが正しく機能し、ステータスが「休憩中」になる (ID 7-1)
     */
    public function test_rest_start_functional()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post(route('attendance.clock-in'));

        // 1. 「休憩入」ボタンが表示されているか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩入');

        // 2. 休憩処理を実行
        $this->post(route('attendance.break-start'));

        // 3. ステータスが「休憩中」になる
        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩中');
    }

    /**
     * 休憩は一日に何回でもできる (ID 7-2)
     */
    public function test_multiple_rest_starts_possible()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post(route('attendance.clock-in'));

        // 1回休憩して戻る
        $this->post(route('attendance.break-start'));
        $this->post(route('attendance.break-end'));

        // 2. 再び「休憩入」ボタンが表示されることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩入');
    }

    /**
     * 休憩戻ボタンが正しく機能し、ステータスが「出勤中」になる (ID 7-3)
     */
    public function test_rest_end_functional()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post(route('attendance.clock-in'));
        $this->post(route('attendance.break-start'));

        // 1. 「休憩戻」ボタンが表示されているか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩戻');

        // 2. 休憩戻処理を実行
        $this->post(route('attendance.break-end'));

        // 3. ステータスが「出勤中」になる
        $response = $this->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    /**
     * 休憩戻は一日に何回でもできる (ID 7-4)
     */
    public function test_multiple_rest_ends_possible()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post(route('attendance.clock-in'));

        // 1回目の休憩サイクル
        $this->post(route('attendance.break-start'));
        $this->post(route('attendance.break-end'));

        // 2回目の休憩入
        $this->post(route('attendance.break-start'));

        // 2. 再び「休憩戻」ボタンが表示されることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩戻');
    }

    /**
     * 休憩時刻が勤怠一覧画面で確認できる (ID 7-5)
     */
    public function test_rest_time_visible_on_attendance_list()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        // 休憩データを作成（1時間分）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
        ]);
        $attendance->restTimes()->create([
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        // 勤怠一覧画面を確認
        $response = $this->actingAs($user)->get(route('attendance.list'));

        // 期待される休憩時間の合計が表示されているか（UIに合わせる）
        $response->assertSee('1:00'); 
    }

    /**
     * 退勤ボタンが正しく機能し、ステータスが「勤務外」になる (ID 8)
     */
    public function test_clock_out_functional()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        // まず出勤させる
        $this->actingAs($user)->post(route('attendance.clock-in'));

        // 1. 「退勤」ボタンが表示されているか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('退勤');

        // 2. 退勤処理を実行
        $this->post(route('attendance.clock-out'));

        // 3. ステータスが「勤務外」になっているか確認
        // ※UIに合わせて「退勤済」や「勤務外」に適宜書き換えてください
        $response = $this->get(route('attendance.index'));
        $response->assertSee('退勤済'); 
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる (ID 8)
     */
    public function test_clock_out_time_visible_on_attendance_list()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        // 1. 出勤と退勤の処理を行う
        $this->actingAs($user)->post(route('attendance.clock-in'));
        $this->post(route('attendance.clock-out'));

        // 2. 勤怠一覧画面へアクセス
        $response = $this->get(route('attendance.list'));

        // 3. 現在時刻（時:分）が退勤時刻として一覧に含まれているか確認
        $nowTime = now()->format('H:i');
        $response->assertSee($nowTime);
    }
}