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
        // 1. Add the column as nullable first
        Schema::table('grading_periods', function (Blueprint $table) {
            /*
             * TODO: Once the exact allowed values for period_type are finalized, 
             * we need to create a follow-up migration to add a PostgreSQL CHECK 
             * constraint to enforce these specific enum values.
             */
            $table->string('period_type')->nullable()->after('sequence');
        });

        // 2. Backfill existing rows with a default value so they aren't null
        // (Change 'standard' to whatever default value makes sense for your existing data)
        DB::table('grading_periods')->whereNull('period_type')->update(['period_type' => 'standard']);

        // 3. Now that all rows have a value, change the column to NOT NULL
        Schema::table('grading_periods', function (Blueprint $table) {
            $table->string('period_type')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grading_periods', function (Blueprint $table) {
            $table->dropColumn('period_type');
        });
    }
};