<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'description',
        'status',
        'color',
        'image_path',
        'amenities',
    ];

    protected $casts = [
        'amenities' => 'array',
    ];

    // ✅ เพิ่มฟังก์ชันนี้เข้าไปครับ (เพื่อให้ Controller นับยอดจองได้)
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
