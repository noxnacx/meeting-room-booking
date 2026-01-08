<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // เพิ่มคอลัมน์ department_id เชื่อมกับตาราง departments
            // nullable() = เผื่อไว้กรณี User ยังไม่มีสังกัด
            // nullOnDelete() = ถ้าแผนกโดนลบ User ไม่ต้องโดนลบตาม แต่ให้ค่าเป็น null แทน
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete()->after('email');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }

};
