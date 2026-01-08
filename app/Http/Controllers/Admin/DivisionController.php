<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DivisionController extends Controller
{
    // แสดงหน้าจัดการ "กองและแผนก" ในหน้าเดียว
    public function index()
    {
        return Inertia::render('Admin/Organization/Index', [
            // ดึงข้อมูล กอง พร้อม แผนก และนับจำนวนคน
            'divisions' => Division::with(['departments' => function($q) {
                $q->withCount('users');
            }])->get()
        ]);
    }

    // เพิ่มกองใหม่
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Division::create($request->all());
        return back();
    }

    // แก้ไขชื่อกอง
    public function update(Request $request, Division $division)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $division->update($request->all());
        return back();
    }

    // ลบกอง
    public function destroy(Division $division)
    {
        $division->delete();
        return back();
    }
}
