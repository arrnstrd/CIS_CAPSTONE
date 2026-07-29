<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_import_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_import_id')->constrained('bulk_imports')->onDelete('cascade');
            $table->unsignedInteger('row_number');
            $table->string('issue_type');
            $table->string('severity')->default('error');
            $table->string('field')->nullable();
            $table->text('message');
            $table->json('raw_data');
            $table->string('status')->default('unresolved');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('bulk_import_id');
            $table->index('row_number');
            $table->index('severity');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_import_issues');
    }
};
