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
        Schema::create('ft_user_exercise_progress_workout', function (Blueprint $table) {
            $table->id();
            $table->integer('progress_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exercise_id');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('ft_users')
                  ->onDelete('cascade');
                  
            $table->foreign('exercise_id')
                  ->references('id')
                  ->on('ft_workout_program_exercises')
                  ->onDelete('cascade');

            // Unique constraint
            $table->unique(['user_id', 'exercise_id']);
            
            // Additional index on exercise_id
            $table->index('exercise_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ft_user_exercise_progress_workout');
    }
};