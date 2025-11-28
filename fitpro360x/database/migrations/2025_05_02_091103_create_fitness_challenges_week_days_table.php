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
        Schema::create(config('tables.fitness_challenges_week_days'), function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('fitness_challenge_id')->nullable()->comment('ft_fitness_challenges tbl FK');
            $table->integer('week')->default(1)->comment('1=> 1st week, 2=> 2nd week');
            $table->integer('day_number')->nullable()->comment('1=> 1st day, 2=> 2nd day');
            $table->tinyInteger('is_rest_day')->default(0)->comment('0=> Not rest day; 1=Rest Day');
            $table->timestamps();
            $table->softDeletes();

            // Define foreign key constraint if you have a 'ft_workout_programs' table
            $table->foreign('fitness_challenge_id')->references('id')->on(config('tables.fitness_challenges'))->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.fitness_challenges_week_days'));
    }
};
