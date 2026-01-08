<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Division;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash; // ✅ จำเป็นต้องใช้สำหรับการเข้ารหัสรหัสผ่าน

class UserController extends Controller
{
    // 1. แสดงรายชื่อ User พร้อมตัวกรอง (Index)
    public function index(Request $request)
    {
        // โหลด department.division และ division (สำหรับคนที่ไม่มีแผนกแต่มีกอง)
        $query = User::with(['department.division', 'division'])->orderBy('id', 'asc');

        // 🔍 Filter 1: Division (หาทั้งจาก department->division และ division_id โดยตรง)
        if ($request->has('division_id') && $request->division_id) {
            $query->where(function($q) use ($request) {
                // กรณี 1: มีแผนก และแผนกสังกัดกองที่เลือก
                $q->whereHas('department', function($d) use ($request) {
                    $d->where('division_id', $request->division_id);
                })
                // กรณี 2: ไม่มีแผนก แต่สังกัดกองที่เลือกโดยตรง
                ->orWhere('division_id', $request->division_id);
            });
        }

        // 🔍 Filter 2: Department
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // 🔍 Filter 3: Role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // 🔍 Filter 4: Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('nickname', 'like', '%'.$request->search.'%');
            });
        }

        // 📄 Pagination Logic: รับค่า per_page มา (ถ้าไม่ส่งมา default = 10)
        $perPage = $request->input('per_page', 10);

        return Inertia::render('Admin/Users/Index', [
            // เปลี่ยน get() เป็น paginate() และใช้ withQueryString() เพื่อให้เวลากดเปลี่ยนหน้า filter ไม่หาย
            'users' => $query->paginate($perPage)->withQueryString(),

            'divisions' => \App\Models\Division::with('departments')->get(),
            'filters' => $request->only(['division_id', 'department_id', 'search', 'role', 'per_page'])
        ]);
    }

    // 2. หน้าฟอร์มสร้าง User ใหม่ (Create)
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            // ส่งข้อมูลโครงสร้างองค์กรไปให้เลือกใน Dropdown เหมือนหน้า Edit
            'divisions' => Division::with('departments')->get()
        ]);
    }

    // 3. บันทึก User ใหม่ (Store)
    public function store(Request $request)
    {
        // 1. ตรวจสอบความถูกต้อง (Validation)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,sub_admin,user',
            'nickname' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'division_id' => 'nullable|exists:divisions,id', // ✅ เพิ่มบรรทัดนี้เพื่อรองรับการเลือกกองอย่างเดียว
        ], [
            // Custom Error Message ภาษาไทย
            'email.unique' => '❌ อีเมลนี้มีอยู่ในระบบแล้ว กรุณาใช้อีเมลอื่น',
            'password.confirmed' => '❌ รหัสผ่านยืนยันไม่ตรงกับรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'email.required' => 'กรุณากรอกอีเมล',
            'role.required' => 'กรุณาเลือกสิทธิ์การใช้งาน',
        ]);

        // 2. บันทึกลงฐานข้อมูล
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'nickname' => $request->nickname,
            'department_id' => $request->department_id,
            'division_id' => $request->division_id, // ✅ บันทึกกอง
        ]);

        return redirect()->route('admin.users.index');
    }

    // 4. หน้าแก้ไข (Edit)
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            // โหลดกองพร้อมแผนก เพื่อไปทำ Dropdown แบบกลุ่ม
            'divisions' => Division::with('departments')->get()
        ]);
    }

    // 5. อัปเดตข้อมูล (Update)
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:50',
            'role' => ['required', Rule::in(['admin', 'sub_admin', 'user'])],
            'department_id' => 'nullable|exists:departments,id',
            'division_id' => 'nullable|exists:divisions,id', // ✅ เพิ่มบรรทัดนี้
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index');
    }

    // 6. ลบผู้ใช้งาน (Destroy)
    public function destroy(User $user)
    {
         if (auth()->id() === $user->id) return back()->with('error', 'ลบตัวเองไม่ได้');
         $user->delete();
         return back();
    }
}
