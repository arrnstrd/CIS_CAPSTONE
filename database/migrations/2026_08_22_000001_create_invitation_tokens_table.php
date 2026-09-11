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
        Schema::create('invitation_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token_hash', 64)->unique(); // SHA-256 hash of token
            $table->timestamp('expires_at'); // Exactly 7 days (1 week) from creation
            $table->timestamp('used_at')->nullable(); // Single-use tracking
            $table->timestamps();

            // Indexes for efficient lookup
            $table->index(['user_id', 'used_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_tokens');
    }
};
