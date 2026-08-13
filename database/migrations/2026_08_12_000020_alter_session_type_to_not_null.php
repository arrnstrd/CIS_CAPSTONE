<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teaching_assignments', function (Blueprint $table) {
            // Using raw SQL to make the column NOT NULL
            DB::statement('ALTER TABLE teaching_assignments ALTER COLUMN session_type SET NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teaching_assignments', function (Blueprint $table) {
            // Using raw SQL to make the column nullable
            DB::statement('ALTER TABLE teaching_assignments ALTER COLUMN session_type DROP NOT NULL');
        });
    }
};