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
        Schema::create(config('tables.user_meal_preferences'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Foreign ID of Users Table');
            $table->unsignedBigInteger('diet_id')->comment('Foreign ID of Meals Diets Table');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints (if needed)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('SET NULL');
            // $table->foreign('diet_id')->references('id')->on('diets')->onDelete('SET NULL');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.user_meal_preferences'));
    }
};
