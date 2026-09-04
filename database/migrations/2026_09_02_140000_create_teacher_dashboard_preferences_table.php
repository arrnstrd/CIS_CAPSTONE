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
        Schema::create('teacher_dashboard_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('default_view')->default('overview');
            $table->string('dashboard_density')->default('comfortable');
            $table->boolean('show_quick_actions')->default(true);
            $table->boolean('show_grading_progress')->default(true);
            $table->boolean('show_class_health')->default(true);
            $table->boolean('show_at_risk')->default(true);
            $table->boolean('show_recent_activity')->default(true);
            $table->boolean('show_summary_cards')->default(true);
            $table->boolean('show_student_counts')->default(true);
            $table->boolean('show_progress_indicators')->default(true);
            $table->unsignedBigInteger('default_class_id')->nullable();
            $table->string('default_term')->nullable()->default('current');
            $table->string('theme')->default('system');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_dashboard_preferences');
    }
};

