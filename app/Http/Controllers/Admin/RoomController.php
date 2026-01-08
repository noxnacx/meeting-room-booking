<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage; // จำเป็นสำหรับการจัดการไฟล์

class RoomController extends Controller
{
    // แสดงรายการห้องทั้งหมด
    public function index()
    {
        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => Room::all()
        ]);
    }

    // หน้าฟอร์มสร้างห้องใหม่
    public function create()
    {
        return Inertia::render('Admin/Rooms/Create');
    }

    // บันทึกห้องใหม่
    public function store(Request $request)
    {
        // 1. ตรวจสอบข้อมูล (Validation)
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,maintenance', // ตรวจสอบสถานะ
            'color' => 'required|string', // ตรวจสอบรหัสสี
            'image' => 'nullable|image|max:2048', // ตรวจสอบรูปภาพ (ไม่เกิน 2MB)
        ]);

        // 2. จัดการอัปโหลดรูปภาพ
        $imagePath = null;
        if ($request->hasFile('image')) {
            // บันทึกลงใน storage/app/public/rooms
            $imagePath = $request->file('image')->store('rooms', 'public');
        }

        // 3. บันทึกลงฐานข้อมูล
        Room::create([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'description' => $request->description,
            'status' => $request->status,
            'color' => $request->color,
            'image_path' => $imagePath, // เก็บ Path ของรูป
        ]);

        return redirect()->route('admin.rooms.index');
    }

    // หน้าแก้ไขห้อง
    public function edit(Room $room)
    {
        return Inertia::render('Admin/Rooms/Edit', [
            'room' => $room
        ]);
    }

    // อัปเดตข้อมูลห้อง
    public function update(Request $request, Room $room)
    {
        // 1. ตรวจสอบข้อมูล
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,maintenance',
            'color' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        // เตรียมข้อมูลสำหรับอัปเดต (ยกเว้นรูปภาพไว้จัดการแยก)
        $data = $request->only(['name', 'capacity', 'description', 'status', 'color']);

        // 2. จัดการรูปภาพใหม่ (ถ้ามีการอัปโหลด)
        if ($request->hasFile('image')) {
            // ลบรูปเก่าทิ้งก่อน (ถ้ามี)
            if ($room->image_path) {
                Storage::disk('public')->delete($room->image_path);
            }
            // อัปโหลดรูปใหม่
            $data['image_path'] = $request->file('image')->store('rooms', 'public');
        }

        // 3. อัปเดตข้อมูลในฐานข้อมูล
        $room->update($data);

        return redirect()->route('admin.rooms.index');
    }

    // ลบห้อง
    public function destroy(Room $room)
    {
        // 1. ลบไฟล์รูปภาพออกจาก Storage (ถ้ามี)
        if ($room->image_path) {
            Storage::disk('public')->delete($room->image_path);
        }

        // 2. ลบข้อมูลจากฐานข้อมูล
        $room->delete();

        return back();
    }
}
