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
        Schema::create(config('tables.fitness_challenges_exercises'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fitness_challenges_week_days_id')->comment('ft_fitness_challenges_week_days Tbl FK field');
            $table->bigInteger('day_id');
            $table->unsignedBigInteger('exercise_id')->comment('ft_exercises Tbl PK');
            $table->integer('reps')->default(20)->nullable()->comment('Number of repetitions');
            $table->integer('sets')->default(3)->nullable()->comment('Number of sets');
            $table->integer('rest_time')->default(1)->nullable()->comment('Rest Time (Seconds)');
            $table->integer('order')->default(1)->nullable()->comment('Order of the exercise');
            $table->timestamps();
            $table->softDeletes();

            // Define foreign key constraint for workout_week_days_id
            $table->foreign('fitness_challenges_week_days_id')
            ->references('id')
            ->on(config('tables.fitness_challenges_week_days'))
            ->onDelete('cascade')
            ->name('fc_ex_week_day_id_fk');

            $table->foreign('exercise_id')
                    ->references('id')
                    ->on(config('tables.exercises'))
                    ->onDelete('cascade')
                    ->name('fc_ex_exercise_id_fk');

            // Define foreign key constraint for day_id (assuming a 'days' table exists)
            // $table->foreign('day_id')->references('id')->on('days')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fitness_challenges_exercises');
    }
};
