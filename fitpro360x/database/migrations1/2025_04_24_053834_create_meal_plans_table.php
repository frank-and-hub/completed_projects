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
            $table->comment('Master Table');

            $table->id(); // Auto-incrementing primary key
            $table->enum('type', ['Breakfast', 'Lunch', 'Dinner'])->comment('Types of Meals');
            $table->string('title', 255)->comment('Meal Plan Title'); // Meal plan title (e.g., "Breakfast")
            $table->string('image', 255)->nullable(); // Image path/URL (nullable)
            $table->text('description')->nullable(); // Description (nullable)
             $table->text('protein')->nullable()->comment('Amount of protein in grams');
            $table->text('carbs')->nullable()->comment('Amount of carbohydrates in grams');
            $table->text('fat')->nullable()->comment('Amount of fat in grams');
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes(); // deleted_at for soft deletes
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
