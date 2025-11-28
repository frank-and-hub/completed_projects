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
        Schema::create(config('tables.user_workout_plans'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('ft_users')
                ->onDelete('cascade')
                ->comment('Foreign key from ft_users');

            $table->bigInteger('workout_program_id')
                ->comment('Foreign key from ft_workout_programs');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')
                ->default(true)
                ->comment('0=inactive, 1=active');
            $table->integer('current_week')->default(1);
            $table->integer('current_day')->default(1);
            $table->boolean('is_completed')->default(false);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('ft_users')
                ->comment('Admin who assigned this (if applicable)');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('workout_program_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_workout_plans');
    }
};
