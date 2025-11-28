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
        Schema::create(config('tables.meal_plan_ingredients'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger('meal_plan_id')->unsigned()->comment('Foreign key of meal plan');
            // $table->foreignId('meal_plan_id')->constrained(config('tables.meal_plans'))->onDelete('cascade')->comment('Primary Id of Meal Plan');
            $table->string('name', 255)->comment('Ingredient name');
            $table->string('quantity', 100)->nullable()->comment('Ingredient quantity');
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes(); // deleted_at

            // Indexes
            // $table->index('meal_plan_id');
            // $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.meal_plan_ingredients'));
    }
};
