<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    // เพิ่มบรรทัดนี้ครับ: อนุญาตให้บันทึกข้อมูลได้ทุกช่อง
    protected $guarded = [];

    // Relationship: จองโดยใคร
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: จองห้องไหน
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'booking_user');
    }

}
