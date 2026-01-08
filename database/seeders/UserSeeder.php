<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin ใหญ่
        User::create([
            'name' => 'Admin Somchai',
            'nickname' => 'Boss',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // รหัสผ่านคือ password
            'role' => 'admin',
            'avatar' => null,
        ]);

        // 2. Sub Admin (ผู้ช่วย)
        User::create([
            'name' => 'Manager Mana',
            'nickname' => 'M',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'sub_admin',
            'avatar' => null,
        ]);

        // 3. User ทั่วไป (พนักงาน)
        User::create([
            'name' => 'Employee Malee',
            'nickname' => 'Lee',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'avatar' => null,
        ]);
    }
}
