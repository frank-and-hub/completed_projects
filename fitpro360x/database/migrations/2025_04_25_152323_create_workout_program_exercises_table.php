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
        // Schema::create(config('tables.workout_program_exercises'), function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('workout_week_days_id')->comment('ft_workout_week_days Tbl FK field');
        //     $table->unsignedBigInteger('exercise_id')->comment('t_ms_exercises Tbl PK');
        //     $table->bigInteger('day_id');
        //     $table->integer('sets')->default(3)->nullable()->comment('Number of sets');
        //     $table->integer('reps')->default(20)->nullable()->comment('Number of repetitions');
        //     $table->integer('rest_seconds')->default(60)->nullable()->comment('Rest time in seconds');
        //     $table->integer('order')->default(1)->nullable()->comment('Order of the exercise');
        //     $table->timestamps();
        //     $table->softDeletes();

        //     // Define foreign key constraint for workout_week_days_id
        //     $table->foreign('workout_week_days_id')->references('id')->on(config('tables.workout_week_days'))->onDelete('cascade');

        //     // Define foreign key constraint for exercise_id (assuming 't_ms_exercises' table exists)
        //     $table->foreign('exercise_id')->references('id')->on(config('tables.exercises'))->onDelete('cascade');

        //     // Define foreign key constraint for day_id (assuming a 'days' table exists)
        //     // $table->foreign('day_id')->references('id')->on('days')->onDelete('cascade');
        // });


        Schema::create(config('tables.workout_program_exercises'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workout_week_days_id')->comment('ft_workout_week_days Tbl FK field');
            $table->unsignedBigInteger('exercise_id')->nullable()->comment('t_ms_exercises Tbl PK');
            $table->bigInteger('day_id')->nullable()->comment('Day number or custom field (no foreign key)');
            $table->integer('sets')->default(3)->nullable()->comment('Number of sets');
            $table->integer('reps')->default(20)->nullable()->comment('Number of repetitions');
            $table->integer('rest_seconds')->default(60)->nullable()->comment('Rest time in seconds');
            $table->integer('order')->default(1)->nullable()->comment('Order of the exercise');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('workout_week_days_id')
                ->references('id')->on(config('tables.workout_week_days'))
                ->onDelete('cascade');

            $table->foreign('exercise_id')
                ->references('id')->on(config('tables.exercises'))
                ->onDelete('cascade');

            // Note: no foreign key on day_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.workout_program_exercises'));
    }
};
