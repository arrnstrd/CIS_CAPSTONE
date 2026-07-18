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
        if (!Schema::hasColumn('enrollments', 'level')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->string('level')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('enrollments', 'level')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }
    }
};
