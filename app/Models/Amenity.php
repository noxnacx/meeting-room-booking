<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    // ต้องมีบรรทัดนี้ครับ
    protected $fillable = ['name', 'icon'];
}
