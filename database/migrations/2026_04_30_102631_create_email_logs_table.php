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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_log_id')->nullable()->constrained('attendance_logs')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students');
            $table->string('email');
            $table->enum('scan_type', ['IN' , 'OUT' , 'RE_ENTRY' , 'RE_EXIT']);
            $table->enum('status' , ['pending' , 'sent' , 'failed']);


            $table->integer('attempt_count')->default(0);
            $table->dateTime('last_attempt_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index('email');
            $table->index('attendance_log_id');
            $table->index(['status', 'attempt_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
