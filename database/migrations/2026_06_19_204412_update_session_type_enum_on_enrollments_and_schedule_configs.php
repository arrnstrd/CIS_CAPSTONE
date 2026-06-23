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
        DB::statement("
            ALTER TABLE enrollments
            MODIFY session_type ENUM (
            'morning',
            'afternoon',
            'whole_day'
             ) NOT NULL
        ");

        DB::statement("
            ALTER TABLE schedule_configs
            MODIFY session_type ENUM (
            'morning',
            'afternoon',
            'whole_day'
            ) NOT NULL
        
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            DB::statement("
            ALTER TABLE enrollments
            MODIFY session_type ENUM(
                'morning',
                'afternoon'
            ) NOT NULL
        ");

        DB::statement("
            ALTER TABLE schedule_configs
            MODIFY session_type ENUM(
                'morning',
                'afternoon'
            ) NOT NULL
        ");
    }
};
