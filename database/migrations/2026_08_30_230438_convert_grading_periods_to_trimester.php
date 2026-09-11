<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert the existing 4-quarter setup into a 3-term trimester setup.
     */
    public function up(): void
    {
        // Rename the first three grading periods to Term 1, Term 2, and Term 3.
        DB::table('grading_periods')
            ->where('sequence', 1)
            ->update([
                'name' => 'Term 1',
                'period_type' => 'trimester',
                'is_active' => true,
            ]);

        DB::table('grading_periods')
            ->where('sequence', 2)
            ->update([
                'name' => 'Term 2',
                'period_type' => 'trimester',
                'is_active' => true,
            ]);

        DB::table('grading_periods')
            ->where('sequence', 3)
            ->update([
                'name' => 'Term 3',
                'period_type' => 'trimester',
                'is_active' => true,
            ]);

        // Keep the old 4th quarter record for data integrity,
        // but remove it from the active 3-term grading cycle.
        DB::table('grading_periods')
            ->where('sequence', 4)
            ->update([
                'name' => '4th Quarter (Legacy)',
                'period_type' => 'quarter',
                'is_active' => false,
            ]);
    }

    /**
     * Restore the original four-quarter setup.
     */
    public function down(): void
    {
        DB::table('grading_periods')
            ->where('sequence', 1)
            ->update([
                'name' => '1st Quarter',
                'period_type' => 'trimester',
                'is_active' => true,
            ]);

        DB::table('grading_periods')
            ->where('sequence', 2)
            ->update([
                'name' => '2nd Quarter',
                'period_type' => 'trimester',
                'is_active' => true,
            ]);

        DB::table('grading_periods')
            ->where('sequence', 3)
            ->update([
                'name' => '3rd Quarter',
                'period_type' => 'trimester',
                'is_active' => true,
            ]);

        DB::table('grading_periods')
            ->where('sequence', 4)
            ->update([
                'name' => '4th Quarter',
                'period_type' => 'trimester',
                'is_active' => true,
            ]);
    }
};