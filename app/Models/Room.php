<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    // เพิ่มบรรทัดนี้: อนุญาตให้เติมข้อมูลได้ทุกช่อง (หรือจะใช้ $fillable ก็ได้)
    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

}
