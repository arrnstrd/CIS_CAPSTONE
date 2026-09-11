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
        Schema::create('student_assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('score', 5, 2);
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'enrollment_id'], 'student_assessment_scores_unique');
            $table->index('enrollment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assessment_scores');
    }
};
