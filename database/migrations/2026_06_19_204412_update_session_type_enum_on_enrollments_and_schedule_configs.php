<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

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
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

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
