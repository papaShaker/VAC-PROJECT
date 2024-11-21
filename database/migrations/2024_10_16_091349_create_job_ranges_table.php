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
        Schema::create('job_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_type')->constrained('contract_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('user_id');
            $table->boolean('work_on_freedays');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_ranges');
    }
};
