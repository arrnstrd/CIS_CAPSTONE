<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyPeriodIds = DB::table('grading_periods')
            ->where('period_type', 'quarter')
            ->pluck('id');

        if ($legacyPeriodIds->isNotEmpty()) {
            $hasLegacyAssessments = DB::table('assessments')
                ->whereIn('grading_period_id', $legacyPeriodIds)
                ->exists();
            $hasLegacyGrades = DB::table('quarterly_grades')
                ->whereIn('grading_period_id', $legacyPeriodIds)
                ->exists();

            if ($hasLegacyAssessments || $hasLegacyGrades) {
                throw new \RuntimeException(
                    'Cannot remove legacy quarterly periods with dependent assessment or grade records. Archive the historical data before retrying this migration.'
                );
            }

            DB::table('grading_periods')->whereIn('id', $legacyPeriodIds)->delete();
        }

        DB::table('grading_periods')->update(['period_type' => 'trimester']);
        DB::table('assessment_categories')
            ->where('name', 'Quarterly Assessment')
            ->update(['name' => 'Term Assessment']);

        DB::statement('ALTER TABLE grading_periods DROP CONSTRAINT IF EXISTS grading_periods_period_type_check');
        DB::statement("ALTER TABLE grading_periods ADD CONSTRAINT grading_periods_period_type_check CHECK (period_type = 'trimester')");

        Schema::rename('quarterly_grades', 'term_grades');
        DB::statement('ALTER TABLE term_grades RENAME COLUMN quarterly_assessment_grade TO term_assessment_grade');
        DB::statement('ALTER TABLE term_grades RENAME CONSTRAINT quarterly_grades_unique TO term_grades_unique');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE term_grades RENAME CONSTRAINT term_grades_unique TO quarterly_grades_unique');
        DB::statement('ALTER TABLE term_grades RENAME COLUMN term_assessment_grade TO quarterly_assessment_grade');
        Schema::rename('term_grades', 'quarterly_grades');

        DB::statement('ALTER TABLE grading_periods DROP CONSTRAINT IF EXISTS grading_periods_period_type_check');
        DB::statement("ALTER TABLE grading_periods ADD CONSTRAINT grading_periods_period_type_check CHECK (period_type IN ('trimester', 'quarter'))");
        DB::table('assessment_categories')
            ->where('name', 'Term Assessment')
            ->update(['name' => 'Quarterly Assessment']);
    }
};
