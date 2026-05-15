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
    Schema::table('enrollments', function (Blueprint $table) {

        // drop foreign key first
        $table->dropForeign('enrollments_adviser_id_foreign');

        // then drop column
        $table->dropColumn([
            'level',
            'adviser_id'
        ]);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('level')->nullable();
            $table->string('adviser_id')->nullable();
        });
    }
};
