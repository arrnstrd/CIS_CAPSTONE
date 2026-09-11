<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update enrollments table
        DB::statement('ALTER TABLE enrollments DROP CONSTRAINT IF EXISTS enrollments_session_type_check;');
        DB::statement("ALTER TABLE enrollments ADD CONSTRAINT enrollments_session_type_check CHECK (((session_type)::text = ANY ((ARRAY['morning'::character varying, 'afternoon'::character varying, 'whole_day'::character varying])::text[])));");

        // 2. Update attendance_logs table
        DB::statement('ALTER TABLE attendance_logs DROP CONSTRAINT IF EXISTS attendance_logs_session_type_check;');
        DB::statement("ALTER TABLE attendance_logs ADD CONSTRAINT attendance_logs_session_type_check CHECK (((session_type)::text = ANY ((ARRAY['morning'::character varying, 'afternoon'::character varying, 'whole_day'::character varying])::text[])));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enrollments table back to morning/afternoon only
        DB::statement('ALTER TABLE enrollments DROP CONSTRAINT IF EXISTS enrollments_session_type_check;');
        DB::statement("ALTER TABLE enrollments ADD CONSTRAINT enrollments_session_type_check CHECK (((session_type)::text = ANY ((ARRAY['morning'::character varying, 'afternoon'::character varying])::text[])));");

        // Revert attendance_logs table back to morning/afternoon only
        DB::statement('ALTER TABLE attendance_logs DROP CONSTRAINT IF EXISTS attendance_logs_session_type_check;');
        DB::statement("ALTER TABLE attendance_logs ADD CONSTRAINT attendance_logs_session_type_check CHECK (((session_type)::text = ANY ((ARRAY['morning'::character varying, 'afternoon'::character varying])::text[])));");
    }
};
