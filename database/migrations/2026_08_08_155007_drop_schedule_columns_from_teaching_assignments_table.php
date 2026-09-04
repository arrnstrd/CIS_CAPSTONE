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
            // Drop the columns
            $table->dropColumn(['in_start', 'late_threshold', 'out_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teaching_assignments', function (Blueprint $table) {

            $table->time('in_start')->after('session_type');
            $table->time('late_threshold')->after('in_start');
            $table->time('out_end')->after('late_threshold');
        });
    }
};
