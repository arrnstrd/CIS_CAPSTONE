<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_verifications', function (Blueprint $table) {
            // 1. Make attendance_log_id nullable (so teachers can mark absent even if no QR scan exists)
            $table->foreignId('attendance_log_id')->nullable()->change();

            // 2. Add new columns for per-subject tracking
            $table->foreignId('enrollment_id')->after('attendance_log_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('teaching_assignment_id')->after('enrollment_id')->constrained('teaching_assignments')->cascadeOnDelete();
            $table->date('attendance_date')->after('teaching_assignment_id');
            
            // 3. Add resolved_by with a default value
            $table->string('resolved_by')->default('teacher')->after('attendance_date');
        });

        // 4. Add status constraint to only allow the 5 specific classroom statuses
        DB::statement('ALTER TABLE attendance_verifications DROP CONSTRAINT IF EXISTS attendance_verifications_status_check;');
        DB::statement("ALTER TABLE attendance_verifications ADD CONSTRAINT attendance_verifications_status_check CHECK (((status)::text = ANY ((ARRAY['present'::character varying, 'late'::character varying, 'absent'::character varying, 'not_in_classroom'::character varying, 'excused'::character varying])::text[])));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop the status constraint
        DB::statement('ALTER TABLE attendance_verifications DROP CONSTRAINT IF EXISTS attendance_verifications_status_check;');

        Schema::table('attendance_verifications', function (Blueprint $table) {
            // 2. Drop the newly added columns
            $table->dropColumn(['resolved_by', 'attendance_date', 'teaching_assignment_id', 'enrollment_id']);
            
            // 3. Revert attendance_log_id to NOT NULL
            $table->foreignId('attendance_log_id')->nullable(false)->change();
        });
    }
};