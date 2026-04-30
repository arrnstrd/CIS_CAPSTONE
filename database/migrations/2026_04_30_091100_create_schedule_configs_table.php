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
        Schema::create('schedule_configs', function (Blueprint $table) {
            $table->id();
            
            $table->enum('level' , ['elementary' , 'hs' , 'shs']);
            $table->enum('session_type' , ['morning' , 'afternoon', 'whole_day']);
    
            $table->time('in_start');
            $table->time('in_end');
             $table->time('late_threshold');
             $table->time('out_start');
             $table->time('out_end');

             $table->timestamps();

            $table->unique(['level' , 'session']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_configs');
    }
};
