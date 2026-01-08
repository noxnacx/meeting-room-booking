<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Division;
use App\Models\Department;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // แสดงรายชื่อ User ทั้งหมด
    // แสดงรายชื่อ User พร้อม Filter
    public function index(Request $request)
    {
        $query = User::with('department.division')->orderBy('id', 'asc');

        // 🔍 Filter 1: กรองตาม "กอง" (Division)
        if ($request->has('division_id') && $request->division_id) {
            $query->whereHas('department', function($q) use ($request) {
                $q->where('division_id', $request->division_id);
            });
        }

        // 🔍 Filter 2: กรองตาม "แผนก" (Department)
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // 🔍 Filter 3: ค้นหาชื่อ/อีเมล
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        return Inertia::render('Admin/Users/Index', [
            'users' => $query->get(),
            // ส่งข้อมูลกอง+แผนก ไปให้หน้าเว็บทำ Dropdown และ Modal จัดการ
            'divisions' => Division::with('departments')->get(),
            'filters' => $request->only(['division_id', 'department_id', 'search'])
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            // โหลดกองพร้อมแผนก เพื่อไปทำ Dropdown แบบกลุ่ม
            'divisions' => \App\Models\Division::with('departments')->get()
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:50',
            'role' => ['required', Rule::in(['admin', 'sub_admin', 'user'])],
            // 👇 เพิ่มบรรทัดนี้ครับ ไม่งั้นมันจะไม่บันทึกแผนกให้
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index');
    }


    public function destroy(User $user)
    {
         if (auth()->id() === $user->id) return back()->with('error', 'ลบตัวเองไม่ได้');
         $user->delete();
         return back();
    }

}
