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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_number')->unique()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('sex',['female', 'male']);
            $table->string('address');
            $table->date('birthdate');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['last_name', 'first_name']);
            $table->index('student_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
