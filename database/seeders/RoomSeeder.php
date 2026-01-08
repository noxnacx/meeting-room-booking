<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'ห้องกระจก (Glass Room)',
                'capacity' => 4,
                'description' => 'ห้องเล็กสำหรับคุยงาน',
                'status' => 'active',
                'color' => '#3B82F6', // สีฟ้า
            ],
            [
                'name' => 'ห้องประชุมใหญ่ (Board Room)',
                'capacity' => 12,
                'description' => 'สำหรับประชุมผู้บริหาร',
                'status' => 'active',
                'color' => '#EF4444', // สีแดง
            ],
            [
                'name' => 'ห้องอบรม (Training Room)',
                'capacity' => 30,
                'description' => 'ปิดปรับปรุงชั่วคราว',
                'status' => 'maintenance', // ลองปิดปรับปรุง 1 ห้อง
                'color' => '#10B981', // สีเขียว
            ],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
