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
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('actor_email')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('action'); // e.g. "Created User", "Deactivated User", "Attempted Deactivation"
            $table->string('target_type')->default('User');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_identifier')->nullable(); // e.g. target user email or name
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('result', ['success', 'denied'])->default('success');
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['actor_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['result', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
