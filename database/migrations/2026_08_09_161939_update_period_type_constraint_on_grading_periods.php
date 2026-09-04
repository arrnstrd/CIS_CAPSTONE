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
        DB::table('grading_periods')->where('period_type', 'standard')->update(['period_type' => 'trimester']);

        DB::statement("ALTER TABLE grading_periods ALTER COLUMN period_type SET DEFAULT 'trimester';");

        DB::statement('ALTER TABLE grading_periods DROP CONSTRAINT IF EXISTS grading_periods_period_type_check;');
        DB::statement("ALTER TABLE grading_periods ADD CONSTRAINT grading_periods_period_type_check CHECK (((period_type)::text = ANY ((ARRAY['trimester'::character varying, 'quarter'::character varying])::text[])));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE grading_periods DROP CONSTRAINT IF EXISTS grading_periods_period_type_check;');

        DB::statement("ALTER TABLE grading_periods ALTER COLUMN period_type DROP DEFAULT;");

        DB::table('grading_periods')->where('period_type', 'trimester')->update(['period_type' => 'standard']);
    }
};