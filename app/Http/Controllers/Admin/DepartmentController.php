<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Departments/Index', [
            'departments' => Department::withCount('users')->get() // นับจำนวนคนในแผนกมาโชว์ด้วย
        ]);
    }

    public function store(Request $request)
    {
        // ✅ รับ division_id มาด้วย
        $request->validate([
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id'
        ]);

        Department::create($request->all());
        return back();
    }

    public function update(Request $request, Department $department)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $department->update(['name' => $request->name]);
        return back();
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return back();
    }
}
