<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        // 1. เริ่ม Query User
        $query = User::query()->orderBy('name');

        // 🔍 Filter: ค้นหาชื่อ
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nickname', 'like', '%' . $request->search . '%');
        }

        // 🔍 Filter: กอง (Division)
        if ($request->filled('division_id')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('department', fn($d) => $d->where('division_id', $request->division_id))
                  ->orWhere('division_id', $request->division_id);
            });
        }

        // 🔍 Filter: แผนก (Department)
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 2. ดึงข้อมูล User พร้อมรายการจอง (Load Relations ให้ครบเพื่อเอาไปโชว์ใน Modal)
        $users = $query->with([
            'department',
            'bookings' => function($q) use ($date) {
                $q->whereDate('start_time', $date)
                  ->orderBy('start_time')
                  ->with(['room', 'participants', 'user']); // Load ผู้เกี่ยวข้อง
            },
            'meetingInvites' => function($q) use ($date) {
                $q->whereDate('start_time', $date)
                  ->orderBy('start_time')
                  ->with(['room', 'participants', 'user']);
            }
        ])
        ->get()
        ->map(function ($user) {
            // รวมรายการจองเป็นไทม์ไลน์เดียว
            $allBookings = $user->bookings->merge($user->meetingInvites);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'department' => $user->department ? $user->department->name : '-',
                'schedule' => $allBookings->map(function($b) {
                    return [
                        'id' => $b->id,
                        'title' => $b->title,
                        'room' => $b->room->name ?? 'Unknown Room',
                        'start' => date('H:i', strtotime($b->start_time)),
                        'end' => date('H:i', strtotime($b->end_time)),

                        // ข้อมูลสำหรับ Modal
                        'full_start' => $b->start_time,
                        'full_end' => $b->end_time,
                        'organizer' => [
                            'name' => $b->user->name ?? '-',
                            'nickname' => $b->user->nickname ?? '',
                            'avatar' => $b->user->avatar ?? null
                        ],
                        'participants' => $b->participants->map(fn($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'nickname' => $p->nickname,
                            'avatar' => $p->avatar
                        ]),

                        // CSS Position
                        'style' => self::calcStyle($b->start_time, $b->end_time)
                    ];
                })
            ];
        });

        return Inertia::render('Schedule/Index', [
            'users' => $users,
            'selectedDate' => $date,
            'divisions' => Division::with('departments')->get(), // ส่งข้อมูลกองไปทำ Filter
            'filters' => $request->only(['search', 'division_id', 'department_id']) // ส่งค่าเดิมกลับไป
        ]);
    }

    // คำนวณ CSS (ปรับช่วงเวลาเป็น 08:00 - 20:00)
    private static function calcStyle($start, $end)
    {
        $startHour = 8;
        $endHour = 20; // ✅ ขยายถึง 2 ทุ่ม
        $totalMinutes = ($endHour - $startHour) * 60; // 12 ชม. * 60 = 720 นาที

        $s = strtotime($start);
        $e = strtotime($end);

        // หาจุดเริ่มต้น (นาที) นับจาก 08:00
        $startMin = (date('H', $s) * 60 + date('i', $s)) - ($startHour * 60);
        $duration = ($e - $s) / 60;

        // แปลงเป็น %
        $left = max(0, ($startMin / $totalMinutes) * 100);
        $width = min(100, ($duration / $totalMinutes) * 100);

        return "left: {$left}%; width: {$width}%;";
    }
}
