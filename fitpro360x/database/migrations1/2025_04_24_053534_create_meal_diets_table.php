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
        Schema::create(config('tables.meal_diets'), function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // varchar(100)
            $table->text('description')->nullable()->comment('Meals descriptions'); // nullable text
            $table->timestamps(); // creates created_at and updated_at
            $table->softDeletes(); // creates deleted_at

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.meal_diets'));
    }
};
