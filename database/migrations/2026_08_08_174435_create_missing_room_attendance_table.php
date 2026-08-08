<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_attendance', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teaching_assignment_id')
                ->constrained('teaching_assignments')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('attendance_date');
            $table->dateTime('time_in');
            $table->dateTime('time_out')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('attendance_date');
            $table->index(['teaching_assignment_id', 'attendance_date']);
            $table->index(['enrollment_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_attendance');
    }
};