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
        DB::statement('ALTER TABLE flagged_scans DROP CONSTRAINT IF EXISTS flagged_scans_flag_type_check;');
        
        // 2. Add the new constraint (removed invalid_checkout, added early_timeout, KEPT missing_in)
        DB::statement("ALTER TABLE flagged_scans ADD CONSTRAINT flagged_scans_flag_type_check CHECK (((flag_type)::text = ANY ((ARRAY['invalid_qr'::character varying, 'excess_scan'::character varying, 'late_arrival'::character varying, 'duplicate_scan'::character varying, 'early_timeout'::character varying, 'missing_out'::character varying, 'missing_in'::character varying])::text[])));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the previous 7 values
        DB::statement('ALTER TABLE flagged_scans DROP CONSTRAINT IF EXISTS flagged_scans_flag_type_check;');
        DB::statement("ALTER TABLE flagged_scans ADD CONSTRAINT flagged_scans_flag_type_check CHECK (((flag_type)::text = ANY ((ARRAY['invalid_qr'::character varying, 'excess_scan'::character varying, 'late_arrival'::character varying, 'duplicate_scan'::character varying, 'invalid_checkout'::character varying, 'missing_out'::character varying, 'missing_in'::character varying])::text[])));");
    }
};