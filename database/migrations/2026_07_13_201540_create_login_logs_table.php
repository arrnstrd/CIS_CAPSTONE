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
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('email_attempted');
            $table->enum('status', ['success', 'failed', 'locked_out']);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->dateTime('attempted_at');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['user_id', 'attempted_at']);
            $table->index(['email_attempted', 'attempted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
