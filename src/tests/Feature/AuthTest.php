<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase; // テストごとにDBをリセットする魔法の言葉

    /**
     * 名前が未入力の場合、バリデーションメッセージが表示される(ID 1)
     */
    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される(ID 1)
     */
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください' 
        ]);
    }

    /**
     * パスワードが8文字未満の場合、バリデーションメッセージが表示される(ID 1)
     */
    public function test_password_length_check()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567', // 7文字
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください'
        ]);
    }

    /**
     * パスワードが一致しない場合、バリデーションメッセージが表示される (ID 1)
     */
    public function test_password_confirmation_check()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password', // 一致させない
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません'
        ]);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される (ID 1)
     */
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '', // 未入力
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    /**
     * フォームに内容が入力されていた場合、データが正常に保存される(ID 1)
     */
    public function test_user_can_register()
    {
        $userData = [
            'name' => 'テスト太郎',
            'email' => 'register@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        // データベースに保存されているか
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'register@example.com',
        ]);

        // 登録後は（通常）リダイレクトされる
        $response->assertStatus(302);
    }

    /**
     * ログイン時、メールアドレスが未入力の場合 (ID 2)
     */
    public function test_login_email_is_required()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    /**
     * ログイン時、パスワードが未入力の場合 (ID 2)
     */
    public function test_login_password_is_required()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    /**
     * 登録内容と一致しない場合 (ID 2)
     */
    public function test_login_failed_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password', 
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }

    /**
 * 管理者ログイン：メールアドレスが未入力の場合 (ID 3)
 */
public function test_admin_login_email_is_required()
{
    $response = $this->post('/admin/login', [
        'email' => '',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'メールアドレスを入力してください'
    ]);
}

/**
 * 管理者ログイン：パスワードが未入力の場合 (ID 3)
 */
public function test_admin_login_password_is_required()
{
    $response = $this->post('/admin/login', [
        'email' => 'admin@example.com',
        'password' => '',
    ]);

    $response->assertSessionHasErrors([
        'password' => 'パスワードを入力してください'
    ]);
}

    /**
     * 管理者ログイン：登録内容と一致しない場合 (ID 3)
     */
    public function test_admin_login_failed_with_invalid_credentials()
    {
        // 管理者ユーザーを作成（role => 1）
        $admin = User::factory()->create([
            'role' => 1,
            'password' => bcrypt('admin-pass'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }

    /**
     * 会員登録後に認証メールが送信される (ID 16)
     */
    public function test_verification_email_sent_after_registration()
    {
        \Illuminate\Support\Facades\Event::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // ユーザー作成直後にメール送信イベントが発行されたか確認
        \Illuminate\Support\Facades\Event::assertDispatched(\Illuminate\Auth\Events\Registered::class);
    }

    /**
     * メール認証誘導画面で「認証はこちらから」ボタン（またはリンク）を確認する (ID 16)
     */
    public function test_unverified_user_is_redirected_to_verification_notice()
    {
        // 認証メール未確認のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証が必要なページ（打刻画面など）にアクセス
        $response = $this->actingAs($user)->get(route('attendance.index'));

        // 1. 認証誘導画面（email.verification.notice）にリダイレクトされること
        $response->assertRedirect('/email/verify');

        // 2. 誘導画面に「認証」に関する文言やボタンが存在することを確認
        $followUpResponse = $this->actingAs($user)->get('/email/verify');
        $followUpResponse->assertStatus(200);
        // 期待挙動: 「認証はこちらから」というテキスト（またはボタン）があること
        $followUpResponse->assertSee('認証はこちらから'); 
    }

    /**
     * メール認証を完了すると、勤怠登録画面に遷移する (ID 16)
     */
    public function test_user_can_access_attendance_after_verification()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 擬似的にメール認証を完了させる（verified_at に時刻を入れる）
        $user->markEmailAsVerified();

        // 勤怠登録画面にアクセス
        $response = $this->actingAs($user)->get(route('attendance.index'));

        // 期待挙動: 正常に表示されること
        $response->assertStatus(200);
        $response->assertViewIs('attendance.index');
    }
}