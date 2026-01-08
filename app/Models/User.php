<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',          // admin, sub_admin, user
        'nickname',
        'department_id', // สังกัดแผนก
        'division_id',   // สังกัดกอง
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- ✅ 1. Helper Functions สำหรับเช็คสิทธิ์ (แก้ Error ที่คุณเจอ) ---
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSubAdmin()
    {
        return $this->role === 'sub_admin';
    }

    // --- ✅ 2. ความสัมพันธ์ (Relations) ---

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    // การประชุมที่ "ฉันเป็นคนจอง"
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // การประชุมที่ "ฉันได้รับเชิญ" (ผ่านตารางกลาง booking_user)
    public function meetingInvites()
    {
        return $this->belongsToMany(Booking::class, 'booking_user');
    }

    // --- ✅ 3. ฟังก์ชันเช็คคิวว่าง (Core Logic) ---
    public function isAvailable($startTime, $endTime)
    {
        // เช็ค: ติดประชุมที่ตัวเองเป็นคนนัดไหม?
        $busyAsOrganizer = $this->bookings()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })->exists();

        // เช็ค: ติดประชุมที่คนอื่นเชิญไหม?
        $busyAsParticipant = $this->meetingInvites()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })->exists();

        // ถ้าไม่ติดทั้งสองอย่าง = ว่าง
        return !$busyAsOrganizer && !$busyAsParticipant;
    }
}
