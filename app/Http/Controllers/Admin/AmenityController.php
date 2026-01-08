<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AmenityController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Amenities/Index', [
            'amenities' => Amenity::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'icon' => 'required']);
        Amenity::create($request->all());
        return back();
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->delete();
        return back();
    }
}
