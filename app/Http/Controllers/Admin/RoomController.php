<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Models\Amenity; // ✅ ต้องมีบรรทัดนี้

class RoomController extends Controller
{
    // แสดงรายการห้องทั้งหมด
    public function index()
    {
        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => Room::all(),
            'allAmenities' => Amenity::all()->keyBy('id')
        ]);
    }

    // หน้าฟอร์มสร้างห้องใหม่
    public function create()
    {
        return Inertia::render('Admin/Rooms/Create', [
            'amenitiesOptions' => Amenity::all()
        ]);
    }

    // บันทึกห้องใหม่
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,maintenance',
            'color' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'amenities' => 'nullable|array',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rooms', 'public');
        }

        Room::create([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'description' => $request->description,
            'status' => $request->status,
            'color' => $request->color,
            'image_path' => $imagePath,
            'amenities' => $request->amenities,
        ]);

        return redirect()->route('admin.rooms.index');
    }

    // --- จุดที่เคยมีปัญหา (Edit) ---
    public function edit(Room $room)
    {
        // ลองเช็คดูครับว่าฟังก์ชันนี้ถูกเรียกจริงไหม ถ้ายังไม่ขึ้น ให้ลอง uncomment บรรทัดข้างล่างนี้เพื่อ debug
        // dd(Amenity::all());

        return Inertia::render('Admin/Rooms/Edit', [
            'room' => $room,
            // 👇 ต้องส่งตัวแปรนี้ไปครับ ไม่งั้นหน้าแก้ไขจะไม่มีให้เลือก
            'amenitiesOptions' => Amenity::all()
        ]);
    }
    // ----------------------------

    // อัปเดตข้อมูลห้อง
    public function update(Request $request, Room $room)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,maintenance',
            'color' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'amenities' => 'nullable|array',
        ]);

        $data = $request->only(['name', 'capacity', 'description', 'status', 'color', 'amenities']);

        if ($request->hasFile('image')) {
            if ($room->image_path) {
                Storage::disk('public')->delete($room->image_path);
            }
            $data['image_path'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($data);

        return redirect()->route('admin.rooms.index');
    }

    // ลบห้อง
    public function destroy(Room $room)
    {
        if ($room->image_path) {
            Storage::disk('public')->delete($room->image_path);
        }
        $room->delete();
        return back();
    }
}
