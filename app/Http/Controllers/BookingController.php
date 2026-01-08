<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Carbon\Carbon;

class BookingController extends Controller
{
    // 1. หน้าแสดงประวัติการจองของฉัน (My Bookings)
    public function index()
    {
        $user = auth()->user();

        // เริ่ม Query
        $query = Booking::with('room')->orderBy('start_time', 'desc');

        // ถ้าไม่ใช่ Admin และไม่ใช่ Sub Admin -> ให้เห็นแค่ของตัวเอง + ที่ตัวเองถูกเชิญ
        if (!$user->isAdmin() && !$user->isSubAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('participants', function ($p) use ($user) {
                      $p->where('users.id', $user->id);
                  });
            });
        }
        // ถ้าเป็น Admin/Sub Admin จะไม่เข้าเงื่อนไขข้างบน = เห็นทั้งหมด (All Bookings)

        $bookings = $query->get();

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings
        ]);
    }

    // 2. หน้าฟอร์มจอง (มีอยู่แล้ว)
    public function create(Room $room)
    {
        // +++ เพิ่มการตรวจสอบตรงนี้ครับ +++
        if ($room->status !== 'active') {
            // ถ้าห้องไม่ Active ให้ดีดกลับหน้า Dashboard หรือแสดง Error 403
            return redirect()->route('dashboard')->with('error', 'ห้องนี้ปิดปรับปรุงชั่วคราว');
        }

        // โค้ดเดิม...
        $users = \App\Models\User::where('id', '!=', auth()->id())
            ->orderBy('nickname', 'asc')
            ->get(['id', 'name', 'nickname', 'email']);

        return Inertia::render('Bookings/Create', [
            'room' => $room,
            'users' => $users
        ]);
    }

    // 3. บันทึกการจอง (มีอยู่แล้ว)
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id,status,active',
            'title' => 'required|string|max:255',
            'booking_date' => 'required|date|after_or_equal:today', // วันที่
            'start_time' => 'required|date_format:H:i', // เวลาเริ่ม (ชั่วโมง:นาที)
            'end_time' => 'required|date_format:H:i|after:start_time', // เวลาจบ
            'participants' => 'array',
        ]);

        // รวมร่าง: เอา วันที่ + เวลา มาต่อกัน เพื่อเก็บลง Database
        $startDateTime = $request->booking_date . ' ' . $request->start_time;
        $endDateTime = $request->booking_date . ' ' . $request->end_time;

        // เช็คห้องว่าง (Collision Check) ด้วยเวลาที่รวมร่างแล้ว
        $exists = Booking::where('room_id', $request->room_id)
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('start_time', [$startDateTime, $endDateTime])
                      ->orWhereBetween('end_time', [$startDateTime, $endDateTime])
                      ->orWhere(function ($q) use ($startDateTime, $endDateTime) {
                          $q->where('start_time', '<', $startDateTime)
                            ->where('end_time', '>', $endDateTime);
                      });
            })->exists();

        if ($exists) {
            return back()->withErrors(['start_time' => 'เวลานี้ห้องไม่ว่างแล้วครับ']);
        }

        // บันทึกข้อมูล
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

        return redirect()->route('bookings.index');
    }

    // 4. ยกเลิกการจอง
    public function destroy(Booking $booking)
    {
        $user = auth()->user();

        // อนุญาตถ้าเป็นเจ้าของ หรือ Admin หรือ Sub Admin
        if ($booking->user_id !== $user->id && !$user->isAdmin() && !$user->isSubAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์ยกเลิกการจองนี้');
        }

        $booking->delete();

        return back();
    }

    public function edit(Booking $booking)
    {
        // เช็คสิทธิ์: ต้องเป็นเจ้าของ หรือ Admin หรือ Sub Admin เท่านั้น
        $user = auth()->user();
        if ($booking->user_id !== $user->id && !$user->isAdmin() && !$user->isSubAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขการจองนี้');
        }

        // โหลดข้อมูลห้องทั้งหมด (เผื่อเปลี่ยนห้อง) และเพื่อนๆ (เผื่อเปลี่ยนคนเชิญ)
        $rooms = Room::where('status', 'active')->get();
        $users = \App\Models\User::where('id', '!=', $booking->user_id)->get();

        return Inertia::render('Bookings/Edit', [
            'booking' => $booking->load('participants'), // โหลดคนถูกเชิญมาด้วย
            'rooms' => $rooms,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        // ... (เช็คสิทธิ์เหมือนเดิม) ...
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

        // รวมร่างเหมือนกัน
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

                    // คำนวณตำแหน่งสำหรับ CSS (สมมติเวลาทำการ 08:00 - 18:00 = 10 ชั่วโมง = 600 นาที)
                    // สูตรคร่าวๆ เพื่อส่งไปคำนวณต่อ หรือคำนวณหน้าบ้านก็ได้
                ];
            });

        return response()->json($bookings);
    }

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
}
