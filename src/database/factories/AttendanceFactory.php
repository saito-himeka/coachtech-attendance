<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            // Userを自動生成して紐付ける
            'user_id' => User::factory(), 
            // マイグレーションのカラム名に合わせて設定 [cite: 7]
            'date' => $this->faker->date(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ];
    }
}