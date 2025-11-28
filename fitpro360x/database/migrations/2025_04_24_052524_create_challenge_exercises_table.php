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
        Schema::create(config('tables.challenge_exercises'), function (Blueprint $table) {
            $table->id(); // Creates auto-incrementing bigint 'id' column
            $table->bigInteger('challenge_id')->unsigned()->comment('Challenge reference');
            $table->integer('week_number')->unsigned()->comment('Week number');
            $table->integer('day_number')->unsigned()->comment('Day number');
            $table->bigInteger('exercise_id')->unsigned()->comment('Exercise reference');
            $table->integer('position')->default(1)->nullable()->comment('Exercise position');
            $table->timestamps(); // Creates created_at and updated_at columns
            $table->softDeletes(); // Creates deleted_at column

            // Add foreign key constraints if needed
            // $table->foreign('challenge_id')->references('id')->on('challenges');
            // $table->foreign('exercise_id')->references('id')->on('exercises');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.challenge_exercises'));
    }
};
