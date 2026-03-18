<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
        'rest_times',
        'remarks',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rest_times' => 'array', // JSON → 配列に自動変換
        'status' => 'integer',
    ];

    /**
     * 勤怠記録とのリレーション（1対多の逆）
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * ステータスが承認待ちかチェック
     */
    public function isPending()
    {
        return $this->status === 0;
    }

    /**
     * ステータスが承認済みかチェック
     */
    public function isApproved()
    {
        return $this->status === 1;
    }

    /**
     * ステータスのテキスト表示
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 0 ? '承認待ち' : '承認済み';
    }
}