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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('capacity');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            // เพิ่มสถานะ: active (ปกติ), maintenance (ปิดปรับปรุง)
            $table->string('status')->default('active');

            // เพิ่มสีประจำห้อง (สำหรับโชว์ในปฏิทิน) เช่น #FF0000
            $table->string('color')->default('#3B82F6');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
