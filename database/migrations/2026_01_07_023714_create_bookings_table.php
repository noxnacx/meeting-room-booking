<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // เชื่อมกับตาราง Users (ใครจอง)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // เชื่อมกับตาราง Rooms (จองห้องไหน)
            $table->foreignId('room_id')->constrained()->onDelete('cascade');

            $table->string('title'); // หัวข้อการประชุม
            $table->dateTime('start_time'); // เวลาเริ่ม
            $table->dateTime('end_time'); // เวลาจบ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
