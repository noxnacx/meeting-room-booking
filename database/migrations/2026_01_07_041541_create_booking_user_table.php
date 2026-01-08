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
        Schema::create('booking_user', function (Blueprint $table) {
            $table->id();

            // เชื่อม Booking ID
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');

            // เชื่อม User ID (คนถูกเชิญ)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // (Optional) เผื่ออนาคตอยากทำสถานะ ตอบรับ/ปฏิเสธ ใส่ไว้ก่อนได้ครับ
            $table->string('status')->default('accepted'); // accepted, declined, pending

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_user');
    }
};
