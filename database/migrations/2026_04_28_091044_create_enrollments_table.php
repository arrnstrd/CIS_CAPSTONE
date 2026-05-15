<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->onDelete('cascade');

            $table->string('school_year');
            $table->string('grade_level');
            $table->string('section');

            $table->enum('level', ['elementary', 'hs', 'shs']);
            $table->enum('session_type', ['morning', 'afternoon' ]);

            $table->foreignId('adviser_id')
                ->nullable()
                ->constrained('teachers')
                ->nullOnDelete();

            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->timestamps();

            $table->unique(['student_id', 'school_year'], 'student_id_school_year_unique');
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
