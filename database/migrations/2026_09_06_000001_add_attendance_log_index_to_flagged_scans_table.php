<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flagged_scans', function (Blueprint $table) {
            $table->index('attendance_log_id', 'flagged_scans_attendance_log_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('flagged_scans', function (Blueprint $table) {
            $table->dropIndex('flagged_scans_attendance_log_id_index');
        });
    }
};