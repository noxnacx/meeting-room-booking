<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // แสดงรายชื่อ User ทั้งหมด
    public function index()
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::orderBy('id', 'asc')->get()
        ]);
    }

    // หน้าแก้ไข User
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user
        ]);
    }

    // บันทึกการแก้ไข (เปลี่ยน Role / Nickname)
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:50',
            // บังคับว่า Role ต้องเป็นค่าที่เรากำหนดเท่านั้น
            'role' => ['required', Rule::in(['admin', 'sub_admin', 'user'])],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index');
    }


    public function destroy(\App\Models\User $user)
    {
        // ป้องกันไม่ให้ลบตัวเอง (เดี๋ยวเข้าระบบไม่ได้)
        if (auth()->id() === $user->id) {
            return back()->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $user->delete();

        return back()->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }

}
