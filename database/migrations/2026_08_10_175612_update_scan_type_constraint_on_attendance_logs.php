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
        // 1. Drop the old constraint
        DB::statement('ALTER TABLE attendance_logs DROP CONSTRAINT IF EXISTS attendance_logs_scan_type_check;');
        
        // 2. Add the new constraint allowing only 'IN' and 'OUT'
        DB::statement("ALTER TABLE attendance_logs ADD CONSTRAINT attendance_logs_scan_type_check CHECK (((scan_type)::text = ANY ((ARRAY['IN'::character varying, 'OUT'::character varying])::text[])));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to allowing 'IN', 'OUT', 'RE_ENTRY', 'RE_EXIT'
        DB::statement('ALTER TABLE attendance_logs DROP CONSTRAINT IF EXISTS attendance_logs_scan_type_check;');
        DB::statement("ALTER TABLE attendance_logs ADD CONSTRAINT attendance_logs_scan_type_check CHECK (((scan_type)::text = ANY ((ARRAY['IN'::character varying, 'OUT'::character varying, 'RE_ENTRY'::character varying, 'RE_EXIT'::character varying])::text[])));");
    }
};