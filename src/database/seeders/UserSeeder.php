<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 管理者ユーザー
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 1, // 管理者
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー1
        User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password123'),
            'role' => 0, // 一般ユーザー
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー2
        User::create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'password' => Hash::make('password123'),
            'role' => 0, // 一般ユーザー
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー3
        User::create([
            'name' => '鈴木一郎',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password123'),
            'role' => 0, // 一般ユーザー
            'email_verified_at' => now(),
        ]);
    }
}