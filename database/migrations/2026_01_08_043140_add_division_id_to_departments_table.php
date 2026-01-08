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
        Schema::table('departments', function (Blueprint $table) {
            // เพิ่ม division_id เชื่อมกับตาราง divisions
            $table->foreignId('division_id')->nullable()->constrained()->onDelete('cascade')->after('id');
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
        });
    }

};
