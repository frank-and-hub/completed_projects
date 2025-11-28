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
        Schema::create(config('tables.meal_plan_nutrition'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger('meal_plan_id')->unsigned()->comment('Foreign key of meal plan');
            // $table->foreignId('meal_plan_id')->constrained(config('tables.meal_plans'))->onDelete('cascade');
            $table->decimal('protein_grams', 5, 2)->nullable()->comment('Amount of protein in grams');
            $table->decimal('carbs_grams', 5, 2)->nullable()->comment('Amount of carbohydrates in grams');
            $table->decimal('fat_grams', 5, 2)->nullable()->comment('Amount of fat in grams');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.meal_plan_nutrition'));
    }
};
