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
        Schema::create(config('tables.meal_ingredients'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meal_plans_id');
            $table->text('ingredient')->nullable()->comment('name of the ingredient');
            $table->text('quantity')->nullable()->comment('qty of the ingredient');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('meal_plans_id')->references('id')->on(config('tables.meal_plans'))->onDelete('cascade'); // Assuming 'fit_meal_plans' is the name of the meal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.meal_ingredients'));
    }
};
