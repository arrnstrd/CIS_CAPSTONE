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
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['adviser_id']);
            $table->dropColumn([
                'level',
                'adviser_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('level')->nullable();
            $table->string('adviser_id')->nullable();
        });
    }
};
