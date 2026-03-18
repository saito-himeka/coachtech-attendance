<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\LoginRequest;

class AuthController extends Controller
{
    /**
     * PG07: 管理者ログイン画面表示
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        
        // 認証を試みる
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // 管理者かチェック
            if ($user->role !== 1) {
                Auth::logout();
                return redirect()->route('admin.login')
                    ->withErrors(['email' => 'ログイン情報が登録されていません']);
            }
            
            $request->session()->regenerate();
            
            return redirect()->route('admin.attendance.list');
        }
        
        return redirect()->route('admin.login')
            ->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * 管理者ログアウト
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }
}