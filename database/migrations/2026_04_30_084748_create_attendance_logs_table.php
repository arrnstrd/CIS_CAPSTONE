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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->enum('scan_type' ,['IN', 'OUT', 'RE_ENTRY', 'RE_EXIT']);
            $table->enum('session_type' , ['morning' , 'afternoon' , 'whole_day']);
            $table->dateTime('scan_time');
            $table->foreignId('scanned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_id')->nullable();
           
            $table->timestamps();

            $table->index(['enrollment_id' , 'scan_time']);
            $table->index('scan_time');
            $table->index('scan_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
