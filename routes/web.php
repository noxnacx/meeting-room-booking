<?php

use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Models\Room;
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

    // --- 1. Dashboard ---
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'rooms' => Room::all()
        ]);
    })->name('dashboard');

    Route::get('/api/bookings-by-date', [BookingController::class, 'getBookingsByDate'])->name('api.get-bookings-by-date');

    // --- 2. Booking System (User) ---
    // ดูประวัติการจองของฉัน
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');

    // จองห้อง (ฟอร์ม + บันทึก)
    Route::get('/rooms/{room}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

    // ยกเลิกการจอง
    Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update'); // ใช้ PUT

    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');


    // --- 3. Admin System (จัดการห้องประชุม) ---
    // URL จะเป็น: /admin/rooms, /admin/rooms/create ฯลฯ
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('rooms', RoomController::class);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });


    // --- 4. User Profile ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
