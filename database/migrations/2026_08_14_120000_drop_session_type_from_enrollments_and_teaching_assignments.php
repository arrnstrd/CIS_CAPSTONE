<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * session_type ownership has moved to `sections.session_type`.
     * Enrollments and TeachingAssignments now derive their session type from
     * their related Section, so their independent columns are no longer needed.
     *
     * `attendance_logs.session_type` (historical snapshot) and
     * `schedule_configs.session_type` (schedule lookup dimension) are NOT
     * touched by this migration.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('session_type');
        });

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropColumn('session_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->enum('session_type', ['morning', 'afternoon', 'whole_day'])->nullable();
        });

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->enum('session_type', ['morning', 'afternoon', 'whole_day'])
                ->nullable()
                ->after('school_year_id');
        });
    }
};
