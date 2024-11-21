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
        Schema::create('user_availability', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('users_available')->unsigned(); // Define users_available as tinyint
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade'); // Foreign key reference to Departments table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_availability');
    }
};
