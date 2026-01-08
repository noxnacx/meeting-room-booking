<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name', 'capacity', 'description', 'status', 'color',
        'image_path', 'amenities' // <--- เพิ่มตรงนี้
    ];

    // แปลง JSON จาก Database เป็น Array ให้อัตโนมัติ
    protected $casts = [
        'amenities' => 'array',
    ];
}
