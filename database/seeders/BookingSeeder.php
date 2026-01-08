<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // ดึงข้อมูล User และ Room มาใช้
        $admin = User::where('email', 'admin@example.com')->first();
        $user = User::where('email', 'user@example.com')->first();
        $room1 = Room::first();

        // 1. Admin จองห้อง (วันนี้ เวลา 10:00 - 12:00)
        $booking = Booking::create([
            'user_id' => $admin->id,
            'room_id' => $room1->id,
            'title' => 'ประชุมวางแผนประจำปี',
            'start_time' => Carbon::today()->setHour(10)->setMinute(0),
            'end_time' => Carbon::today()->setHour(12)->setMinute(0),
        ]);

        // 2. Admin เชิญ (Invite) พนักงานทั่วไปเข้าประชุมด้วย
        // บันทึกลงตาราง booking_user
        $booking->participants()->attach($user->id);
    }
}
