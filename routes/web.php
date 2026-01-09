<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ScheduleController;
// Admin Controllers
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\AmenityController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes (ไม่ต้อง Login ก็เข้าได้)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (ต้อง Login ก่อน)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // --- 1. Dashboard & Main Pages ---

    // ✅ แก้ไข: ใช้ BookingController::dashboard แทน function()
    // หน้า Dashboard (สถิติภาพรวม)
    Route::get('/dashboard', [BookingController::class, 'dashboard'])->name('dashboard');

    // ✅ เพิ่มใหม่: หน้าเลือกห้องจอง (ย้ายมาจาก Dashboard เดิม)
    Route::get('/reserve-room', [BookingController::class, 'selectRoom'])->name('bookings.select_room');


    // --- 2. Booking System (User) ---

    // ประวัติการจอง
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');

    // กระบวนการจอง
    Route::get('/rooms/{room}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

    // การจัดการการจอง (ดู, แก้ไข, ยกเลิก)
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');


    // --- 3. Schedules & Calendar ---

    // ตารางงานทีม (Timeline)
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

    // ปฏิทินรวม (Calendar)
    Route::get('/calendar', function () {
        return Inertia::render('Calendar/Index');
    })->name('calendar.index');


    // --- 4. Internal APIs (สำหรับ Ajax/Axios) ---
    Route::get('/api/bookings-by-date', [BookingController::class, 'getBookingsByDate'])->name('api.get-bookings-by-date');
    Route::get('/api/calendar-events', [BookingController::class, 'calendarEvents'])->name('api.calendar-events');


    // --- 5. Admin System ---
    Route::prefix('admin')->name('admin.')->group(function () {

        // จัดการห้องประชุม
        Route::resource('rooms', RoomController::class);

        // จัดการผู้ใช้งาน
        Route::resource('users', UserController::class);

        // จัดการโครงสร้างองค์กร (ใช้แค่บาง Method)
        Route::resource('divisions', DivisionController::class)->only(['store', 'update', 'destroy']);
        Route::resource('departments', DepartmentController::class)->only(['store', 'update', 'destroy']);

        // จัดการสิ่งอำนวยความสะดวก (Amenities)
        Route::get('/amenities', [AmenityController::class, 'index'])->name('amenities.index');
        Route::post('/amenities', [AmenityController::class, 'store'])->name('amenities.store');
        Route::delete('/amenities/{amenity}', [AmenityController::class, 'destroy'])->name('amenities.destroy');
    });


    // --- 6. User Profile ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
