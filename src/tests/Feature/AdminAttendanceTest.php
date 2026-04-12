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


    /** 管理者が全ユーザーの勤怠情報を確認できる (ID 12)**/
    public function test_admin_can_see_all_users_attendance()
    {
        $admin = User::factory()->create(['role' => 1]); // 管理者作成
        $userA = User::factory()->create(['name' => 'ユーザーA']);
        $userB = User::factory()->create(['name' => 'ユーザーB']);

        // AさんとBさんの今日のデータを作成
        Attendance::factory()->create(['user_id' => $userA->id, 'date' => now()->format('Y-m-d')]);
        Attendance::factory()->create(['user_id' => $userB->id, 'date' => now()->format('Y-m-d')]);

        // 管理者で一覧にアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('ユーザーB'); // 全ユーザーが見えること [cite: 8]
    }

    /**
     * 管理者が不適切な時間で修正しようとした場合、エラーメッセージが表示される (ID 13)
     */
    public function test_admin_cannot_update_attendance_with_invalid_time_order()
    {
        $admin = User::factory()->create(['role' => 1]); // 管理者
        $attendance = Attendance::factory()->create(); // 適当な勤怠データ

        // 管理者用の更新ルートへPOST送信
        $response = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), [
            'start_time' => '20:00',
            'end_time' => '09:00',
            'remarks' => '管理者による修正理由',
        ]);

        $response->assertSessionHasErrors(['start_time']);
    }

    /**
     * 管理者が全一般ユーザーの「氏名」「メールアドレス」を確認できる (ID 14)
     */
    public function test_admin_can_see_staff_list()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => 'スタッフ1', 'email' => 'staff1@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee($staff->name);
        $response->assertSee($staff->email); // 氏名とメールが表示されているか 
    }

    /**
     * 修正申請の承認処理が正しく行われる (ID 15)
     */
    public function test_admin_can_approve_correction_request()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();
        
        // 1. 修正前の勤怠データ
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2. 修正申請データ（10:00〜19:00への変更希望）
        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks' => '修正願い',
            'status' => 0, // 承認待ち
        ]);

        // 3. 承認実行
        $response = $this->actingAs($admin)->post(route('admin.stamp_correction_request.process', $request->id));

        // 4. 検証：リダイレクトとデータベースの更新確認 
        $response->assertRedirect(route('admin.stamp_correction_request.list'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);
        $this->assertEquals(1, $request->fresh()->status);
    }

    /**
     * 承認待ちの修正申請が全て表示されている (ID 15)
     */
    public function test_admin_can_see_pending_correction_requests()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 承認待ちの申請を作成
        StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks' => 'テスト申請',
            'status' => 0, // 0: 承認待ち
        ]);

        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('テスト申請');
    }
}