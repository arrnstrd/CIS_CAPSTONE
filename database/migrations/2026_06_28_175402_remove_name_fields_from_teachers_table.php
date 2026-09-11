<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('teachers', function (Blueprint $table) {
            $table->string('first_name')->after('user_id');
            $table->string('last_name')->after('first_name');
        });
    }
};
