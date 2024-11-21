<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('weekly_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->integer('week_number');
            $table->json('schedule_data'); // Store the schedule as JSON
            $table->softDeletes(); // For soft delete
            $table->timestamps();
            
            // Add unique constraint on department_id and week_number
            $table->unique(['department_id', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_schedules');
    }
};
