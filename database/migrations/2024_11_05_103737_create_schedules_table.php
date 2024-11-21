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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week')->unsigned()->comment('1 for Monday, 7 for Sunday');
            $table->time('start_time'); // Time object for start time
            $table->time('end_time');   // Time object for end time
            $table->boolean('is_free_day')->default(false);
            $table->foreignId('user_availability_id')->constrained('user_availability')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
