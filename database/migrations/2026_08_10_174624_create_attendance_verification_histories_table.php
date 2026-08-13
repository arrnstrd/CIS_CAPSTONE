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
        Schema::create('attendance_verification_histories', function (Blueprint $table) {
            $table->id();
            
            // Links to the classroom verification record being audited
            $table->foreignId('attendance_verification_id')->constrained('attendance_verifications')->cascadeOnDelete();
            
            $table->string('previous_status')->nullable();
            $table->string('new_status');     
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->text('remarks')->nullable();
            
            // Audit trails should be immutable, so we only want created_at, no updated_at
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_verification_histories');
    }
};