<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Department; // เพิ่ม use เพื่อความสะอาดของโค้ด

class BookingController extends Controller
{
    // 1. หน้าแสดงประวัติการจอง (เพิ่มตัวกรองห้องประชุม)
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Booking::with(['room', 'user.division', 'user.department', 'participants'])
            ->orderBy('start_time', 'desc');

        // --- Permission Check ---
        if (!$user->isAdmin() && !$user->isSubAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('participants', function ($p) use ($user) {
                      $p->where('users.id', $user->id);
                  });
            });
        }

        // --- 🔍 Filter Logic ---

        // 1. ค้นหา
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. กรองตามห้องประชุม (✅ เพิ่มใหม่)
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // 3. กรองตามกอง
        if ($request->filled('division_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('division_id', $request->division_id);
            });
        }

        // 4. กรองตามแผนก
        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // 5. กรองช่วงเวลา
        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('start_time', '<=', $request->end_date);
        }

        $bookings = $query->paginate(10)->withQueryString();

        // ส่งข้อมูล Master Data ไปทำ Dropdown
        $divisions = \App\Models\Division::with('departments')->orderBy('name')->get();
        $rooms = \App\Models\Room::orderBy('name')->get(); // ✅ ดึงรายชื่อห้องส่งไป

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'divisions' => $divisions,
            'rooms' => $rooms, // ✅ ส่งไปหน้าบ้าน
            'filters' => $request->only(['search', 'room_id', 'division_id', 'department_id', 'start_date', 'end_date'])
        ]);
    }

    // 3. บันทึกการจอง (เพิ่ม Logic ป้องกันคนไม่ว่าง)
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id,status,active',
            'title' => 'required|string|max:255',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'participants' => 'nullable|array',
        ]);

        $startDateTime = $request->booking_date . ' ' . $request->start_time;
        $endDateTime = $request->booking_date . ' ' . $request->end_time;

        // 1. เช็คห้องว่าง (Room Conflict Check)
        $roomBusy = Booking::where('room_id', $request->room_id)
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(function ($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<', $endDateTime)
                      ->where('end_time', '>', $startDateTime);
                });
            })->exists();

        if ($roomBusy) {
            return back()->withErrors(['start_time' => '❌ ห้องไม่ว่างในช่วงเวลานี้']);
        }

        // 🔥 2. เช็คคนว่าง (Participants Conflict Check) 🔥
        if ($request->participants) {
            $busyPeople = [];
            foreach ($request->participants as $userId) {
                $user = User::find($userId);

                // ใช้ฟังก์ชัน isAvailable ที่เราทำไว้ใน User Model
                if ($user && !$user->isAvailable($startDateTime, $endDateTime)) {
                    $busyPeople[] = $user->name . ($user->nickname ? " ({$user->nickname})" : "");
                }
            }

            // ถ้าเจอคนไม่ว่าง -> ดีดกลับพร้อมรายชื่อ
            if (count($busyPeople) > 0) {
                return back()->withErrors([
                    'participants' => '⚠️ ไม่สามารถจองได้ เนื่องจากมีผู้ติดนัดหมายอื่น: ' . implode(', ', $busyPeople) . ' (กรุณาลบรายชื่อออกก่อน)'
                ]);
            }
        }

        // 3. ผ่านทุกด่าน -> บันทึก
        $booking = Booking::create([
            'user_id' => auth()->id(),
            'room_id' => $request->room_id,
            'title' => $request->title,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
        ]);

        if ($request->participants) {
            $booking->participants()->attach($request->participants);
        }

        return redirect()->route('bookings.index')->with('success', 'จองห้องประชุมสำเร็จ!');
    }

    // 4. หน้าแก้ไขการจอง
    // แก้ไขฟังก์ชัน edit
    public function edit(Booking $booking)
    {
        $user = auth()->user();
        if ($booking->user_id !== $user->id && !$user->isAdmin() && !$user->isSubAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขการจองนี้');
        }

        $rooms = Room::where('status', 'active')->get();

        // ✅ 1. ดึง User พร้อม department_id, division_id (เพื่อใช้กรองเชิญกลุ่ม)
        $users = User::where('id', '!=', $booking->user_id) // ไม่เอาตัวเอง (เจ้าของเดิม)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'nickname', 'email', 'avatar', 'department_id', 'division_id']);

        // ✅ 2. ดึงโครงสร้างองค์กร (Divisions -> Departments) ส่งไปหน้าบ้าน
        $divisions = \App\Models\Division::with('departments')->orderBy('name')->get();

        return Inertia::render('Bookings/Edit', [
            'booking' => $booking->load('participants'),
            'rooms' => $rooms,
            'users' => $users,
            'divisions' => $divisions // ส่งเพิ่มไปครับ
        ]);
    }

    // 5. อัปเดตการจอง
    public function update(Request $request, Booking $booking)
    {
        $user = auth()->user();
        if ($booking->user_id !== $user->id && !$user->isAdmin() && !$user->isSubAdmin()) {
            abort(403);
        }

        $request->validate([
            'room_id' => 'required|exists:rooms,id,status,active',
            'title' => 'required|string|max:255',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'participants' => 'array',
        ]);

        // รวมร่างวันเวลา
        $startDateTime = $request->booking_date . ' ' . $request->start_time;
        $endDateTime = $request->booking_date . ' ' . $request->end_time;

        $booking->update([
            'room_id' => $request->room_id,
            'title' => $request->title,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
        ]);

        if ($request->has('participants')) {
            $booking->participants()->sync($request->participants);
        }

        return redirect()->route('bookings.index');
    }

    // 6. ยกเลิกการจอง
    public function destroy(Booking $booking)
    {
        $user = auth()->user();
        if ($booking->user_id !== $user->id && !$user->isAdmin() && !$user->isSubAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์ยกเลิกการจองนี้');
        }

        $booking->delete();
        return back();
    }

    // 7. แสดงรายละเอียดการจอง
    public function show(Booking $booking)
    {
        $user = auth()->user();

        // เช็คสิทธิ์: ต้องเป็น เจ้าของ, Admin, Sub Admin หรือ ผู้ถูกเชิญ เท่านั้นถึงดูได้
        $isParticipant = $booking->participants->contains($user->id);

        if ($booking->user_id !== $user->id && !$user->isAdmin() && !$user->isSubAdmin() && !$isParticipant) {
            abort(403, 'คุณไม่มีสิทธิ์ดูรายละเอียดการจองนี้');
        }

        return Inertia::render('Bookings/Show', [
            'booking' => $booking->load(['room', 'user', 'participants'])
        ]);
    }

    // 8. API: ดึงข้อมูลการจองตามวันที่ (สำหรับ Timeline หน้าจอง)
    public function getBookingsByDate(Request $request)
    {
        $request->validate([
            'room_id' => 'required',
            'date' => 'required|date',
        ]);

        // ดึงการจองทั้งหมดของวันนั้น เรียงตามเวลา
        $bookings = Booking::where('room_id', $request->room_id)
            ->whereDate('start_time', $request->date)
            ->orderBy('start_time')
            ->with('user') // เอาชื่อคนจองมาโชว์
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'title' => $booking->title,
                    // แปลงเวลาให้เป็น HH:mm (เช่น 09:30)
                    'start_time' => date('H:i', strtotime($booking->start_time)),
                    'end_time' => date('H:i', strtotime($booking->end_time)),
                    'booked_by' => $booking->user->name,
                    'avatar' => $booking->user->avatar,
                ];
            });

        return response()->json($bookings);
    }

    // 9. API: สำหรับ FullCalendar
    public function calendarEvents(Request $request)
    {
        // 1. รับค่า start/end จากปฏิทิน และแปลงเป็น format ที่ MySQL เข้าใจ (Y-m-d H:i:s)
        $start = $request->query('start') ? Carbon::parse($request->query('start'))->toDateTimeString() : null;
        $end = $request->query('end') ? Carbon::parse($request->query('end'))->toDateTimeString() : null;

        // 2. Query ข้อมูล
        $bookings = Booking::with(['room', 'user', 'participants']);

        // ถ้ามี start/end ส่งมา ให้กรองตามช่วงเวลา
        if ($start && $end) {
            $bookings->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
            });
        }

        // 3. จัดรูปแบบข้อมูลส่งกลับ (เพิ่ม participants เข้าไปใน extendedProps)
        $events = $bookings->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => $booking->room->name,
                'start' => $booking->start_time,
                'end' => $booking->end_time,
                'backgroundColor' => $booking->room->color ?? '#6b7280',
                'borderColor' => 'transparent',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'topic' => $booking->title,
                    'room_name' => $booking->room->name,
                    'host_name' => $booking->user->name,
                    'host_avatar' => $booking->user->avatar,
                    // ส่งรายชื่อผู้เข้าร่วมไปด้วย
                    'participants' => $booking->participants->map(function($p) {
                         return [
                             'name' => $p->name,
                             'avatar' => $p->avatar
                         ];
                    })
                ]
            ];
        });

        return response()->json($events);
    }

    // ...

    // แก้ไขฟังก์ชัน dashboard
    public function dashboard()
    {
        $user = auth()->user();

        // 1. Stats
        $totalBookings = Booking::whereDate('start_time', now())->count();
        $myBookings = Booking::where('user_id', $user->id)->count(); // ของฉัน (รวมทั้งหมด)

        // 2. Upcoming Meetings (รายการที่จะถึงเร็วๆ นี้ 5 อันดับ)
        // เปลี่ยนจากดูแค่ "วันนี้" เป็น "อนาคต" จะได้เห็นภาพรวมดีกว่า
        $upcomingMeetings = Booking::where('start_time', '>=', now())
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('participants', function($p) use ($user) {
                      $p->where('users.id', $user->id);
                  });
            })
            ->orderBy('start_time', 'asc')
            ->take(5) // เอาแค่ 5 อัน
            ->with('room')
            ->get();

        // 3. Popular Rooms
        $popularRooms = Room::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take(3)
            ->get();

        // 4. ✅ Real-time Room Status (เช็คว่าตอนนี้ห้องว่างไหม)
        $roomStatus = Room::where('status', 'active')->get()->map(function($room) {
            // เช็คว่ามี Booking ไหนที่ "คร่อม" เวลาปัจจุบันอยู่ไหม
            $isBusy = $room->bookings()
                ->where('start_time', '<=', now())
                ->where('end_time', '>', now())
                ->exists();

            $room->current_status = $isBusy ? 'busy' : 'available';
            return $room;
        });

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $totalBookings,
                'mine' => $myBookings,
            ],
            'upcomingMeetings' => $upcomingMeetings, // เปลี่ยนชื่อตัวแปร
            'popularRooms' => $popularRooms,
            'roomStatus' => $roomStatus // ✅ ส่งข้อมูลสถานะห้องไป
        ]);
    }

    // 2. หน้าเลือกห้องจอง (ย้ายมาจาก Dashboard เก่า)
    public function selectRoom()
    {
        $rooms = Room::where('status', 'active')->get(); // เอาเฉพาะห้องที่เปิด
        $allAmenities = \App\Models\Amenity::all()->keyBy('id'); // ดึงอุปกรณ์

        return Inertia::render('Bookings/SelectRoom', [
            'rooms' => $rooms,
            'allAmenities' => $allAmenities
        ]);
    }

}
