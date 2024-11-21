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
        Schema::create('extra_days', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->date('day_worked');
            $table->integer('extra_day_type');
            $table->integer('user_insert');
            $table->integer('user_delete');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_days');
    }
};
