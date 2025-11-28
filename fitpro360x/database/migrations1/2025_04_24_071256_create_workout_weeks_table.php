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
        Schema::create(config('tables.workout_weeks'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id')->comment('Workout Program ID');
            $table->integer('week_number')->comment('Week number');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.workout_weeks'));
    }
};
