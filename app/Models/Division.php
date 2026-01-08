<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = ['name'];

    // 1 กอง มีหลาย แผนก
    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
