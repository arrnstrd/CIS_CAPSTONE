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
        Schema::create('flagged_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_log_id')->nullable()->constrained('attendance_logs')->nullOnDelete();
            $table->enum('flag_type' , ['duplicate_scan' , 'late_arrival' , 'early_out' , 'missing_entry' , 'missing_out' , 'invalid_session' , 'too_early' , 'excess_scan']);
            $table->text('description')->nullable();
            
            $table->timestamps();

            $table->index('flag_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flagged_scans');
    }
};
