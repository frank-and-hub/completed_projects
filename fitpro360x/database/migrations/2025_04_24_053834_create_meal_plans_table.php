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
        Schema::create(config('tables.meal_plans'), function (Blueprint $table) {
            $table->comment('Master Table for Meal Plans'); // Clearer table comment

            $table->id(); // Primary key
            $table->string('title', 255)->nullable()->comment('Title of the meal plan');
            $table->string('image', 255)->nullable()->comment('Image URL for the meal plan');
            $table->text('description')->nullable()->comment('Description of the meal plan');
            $table->integer('type')->default(1)->comment('Meal type: 1 = Breakfast, 2 = Lunch, 3 = Dinner');
            $table->integer('diet_preference')->default(1)->comment('Diet preference: 1 = Vegan,2 = Veg,3 = Non-Veg, 4 = Keto, 5= Mixed');
           $table->double('protein')->nullable()->comment('Amount of protein in grams');
            $table->double('carbs')->nullable()->comment('Amount of carbohydrates in grams');
            $table->double('fat')->nullable()->comment('Amount of fat in grams');
            $table->timestamps(); // Automatically handles created_at and updated_at
            $table->softDeletes(); // Soft delete

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.meal_plans'));
    }
};
