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
        Schema::create('qr_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();

            $table->date('attendance_date');

            $table->foreignId('time_in_log_id')->nullable()->constrained('attendance_logs')->nullOnDelete();
            $table->foreignId('time_out_log_id')->nullable()->constrained('attendance_logs')->nullOnDelete();

            $table->smallInteger('spam_offense_count')->default(0);
            $table->timestamp('cooldown_expires_at')->nullable();

            $table->timestamps();


            $table->unique(['enrollment_id', 'attendance_date'], 'qr_attendances_enrollment_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_attendances');
    }
};
