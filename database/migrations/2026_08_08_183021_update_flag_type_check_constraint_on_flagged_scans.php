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
        // Drop the old constraint
        DB::statement('ALTER TABLE flagged_scans DROP CONSTRAINT IF EXISTS flagged_scans_flag_type_check;');

        // Add the new constraint with your desired values
        DB::statement("ALTER TABLE flagged_scans ADD CONSTRAINT flagged_scans_flag_type_check CHECK (((flag_type)::text = ANY ((ARRAY['invalid_qr'::character varying, 'excess_scan'::character varying, 'late_arrival'::character varying, 'duplicate_scan'::character varying, 'invalid_checkout'::character varying, 'missing_out'::character varying, 'missing_in'::character varying])::text[])));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the old constraint
        DB::statement('ALTER TABLE flagged_scans DROP CONSTRAINT IF EXISTS flagged_scans_flag_type_check;');
        DB::statement("ALTER TABLE flagged_scans ADD CONSTRAINT flagged_scans_flag_type_check CHECK (((flag_type)::text = ANY ((ARRAY['duplicate_scan'::character varying, 'late_arrival'::character varying, 'early_out'::character varying, 'missing_entry'::character varying, 'missing_out'::character varying, 'invalid_session'::character varying, 'too_early'::character varying, 'excess_scan'::character varying])::text[])));");
    }
};
