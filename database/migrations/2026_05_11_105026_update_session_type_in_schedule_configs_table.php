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
        Schema::table('schedule_configs', function (Blueprint $table) {
             $table->enum('session_type', ['morning', 'afternoon'])
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_configs', function (Blueprint $table) {
             $table->enum('session_type', ['morning', 'afternoon'])
                ->change();
        });
    }
};
