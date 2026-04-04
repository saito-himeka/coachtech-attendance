<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================
// 認証不要のルート
// ============================================

// 一般ユーザー：会員登録・ログイン（Fortifyが自動処理）
// /register (GET/POST) - Fortifyが自動生成
// /login (GET/POST) - Fortifyが自動生成

// 管理者：ログイン
Route::prefix('admin')->middleware('guest')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});

// ============================================
// 一般ユーザー（認証必須）
// ============================================

Route::middleware(['auth', 'verified'])->group(function () {
    
    // PG03: 勤怠登録（打刻画面）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break-start');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break-end');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    
    // PG04: 勤怠一覧（月次）
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    
    // PG05: 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    
    // PG06: 申請一覧（一般ユーザー）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])->name('stamp_correction_request.list');
    
    // 修正申請の送信
    Route::post('/stamp_correction_request/store', [StampCorrectionRequestController::class, 'store'])->name('stamp_correction_request.store');
});

// ============================================
// 管理者（認証必須 + 管理者権限）
// ============================================

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    // PG08: 日次勤怠一覧
    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('admin.attendance.list');
    
    // PG09: 勤怠詳細（管理者）
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('admin.attendance.detail');
    
    // 管理者による直接修正
    Route::post('/attendance/{id}/update', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
    
    // PG10: スタッフ一覧
    Route::get('/staff/list', [StaffController::class, 'list'])->name('admin.staff.list');
    
    // PG11: スタッフ別勤怠一覧（月次）
    Route::get('/attendance/staff/{id}', [StaffController::class, 'attendance'])->name('admin.attendance.staff');
    
    // CSV出力（応用機能）
    Route::get('/attendance/staff/{id}/csv', [StaffController::class, 'exportCsv'])->name('admin.attendance.staff.csv');
    
    // PG12: 申請一覧（管理者）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'adminList'])->name('admin.stamp_correction_request.list');
    
    // PG13: 修正申請承認画面
    Route::get('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
    
    // 承認処理
    Route::post('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'processApproval'])->name('admin.stamp_correction_request.process');
    
    // ログアウト
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// ============================================
// リダイレクト
// ============================================

// ルートアクセス時のリダイレクト
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role == 1) {
            return redirect()->route('admin.attendance.list');
        }
        return redirect()->route('attendance.index');
    }
    return redirect()->route('login');
});