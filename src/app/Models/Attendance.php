<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * ユーザーとのリレーション（1対多の逆）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 休憩時間とのリレーション（1対多）
     */
    public function restTimes()
    {
        return $this->hasMany(RestTime::class);
    }

    /**
     * 修正申請とのリレーション（1対多）
     */
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    /**
     * 勤務時間を計算（分単位）
     */
    public function getWorkMinutesAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        // 総勤務時間
        $totalMinutes = $start->diffInMinutes($end);
        
        // 休憩時間を引く
        $breakMinutes = $this->getTotalBreakMinutesAttribute();
        
        return $totalMinutes - $breakMinutes;
    }

    /**
     * 休憩時間の合計を計算（分単位）
     */
    public function getTotalBreakMinutesAttribute()
    {
        $totalMinutes = 0;
        
        foreach ($this->restTimes as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $start = \Carbon\Carbon::parse($rest->start_time);
                $end = \Carbon\Carbon::parse($rest->end_time);
                $totalMinutes += $start->diffInMinutes($end);
            }
        }
        
        return $totalMinutes;
    }
}