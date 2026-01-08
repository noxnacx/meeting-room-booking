<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name'];

    // ความสัมพันธ์: 1 แผนก มี User ได้หลายคน
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
