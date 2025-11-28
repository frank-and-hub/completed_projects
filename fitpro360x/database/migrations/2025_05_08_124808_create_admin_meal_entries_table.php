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
        Schema::create(config('tables.admin_meal_entries'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('workout_program_id')->nullable()->comment('Reference to the workout program id');
            $table->integer('meal_id')->nullable()->comment('Reference to the meal plans id');
            $table->integer('diet_preference')->nullable()->comment('1 = Veg, 2 = Non-Veg, 3 = Keto, 4 = Vegan	');
            $table->timestamps();

            $table->index(['workout_program_id']);
            $table->index(['meal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.admin_meal_entries'));
    }
};
