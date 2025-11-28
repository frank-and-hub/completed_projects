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
        Schema::create(config('tables.user_progress'), function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('Foreign ID of Users Table');
            // $table->integer('exercise_id')->comment('Foreign ID of Exercise Table');
            $table->integer('week_id')->nullable();
            $table->integer('day_id')->nullable();
            $table->integer('workout_program_id')->nullable();
            $table->integer('exercise_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 = false, 1 = partial, 2= completed');
            $table->tinyInteger('is_active')->default(1)->comment('0 = inactive, 1= active');

            // $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints (assuming you have users and exercises tables)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('exercise_id')->references('id')->on('exercises')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.user_progress'));
    }
};
