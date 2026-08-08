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
        Schema::dropIfExists('room_attendance');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('room_attendance', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('teaching_assignment_id');
            $table->bigInteger('enrollment_id');
            $table->date('attendance_date');
            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }
};
