<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable /*implements MustVerifyEmail*/
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'integer',
    ];

    /**
     * 勤怠記録とのリレーション（1対多）
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * 管理者かチェック
     */
    public function isAdmin()
    {
        return $this->role === 1;
    }

    /**
     * 一般ユーザーかチェック
     */
    public function isUser()
    {
        return $this->role === 0;
    }

    /**
     * 役割のテキスト表示
     */
    public function getRoleTextAttribute()
    {
        return $this->role === 1 ? '管理者' : '一般ユーザー';
    }
}