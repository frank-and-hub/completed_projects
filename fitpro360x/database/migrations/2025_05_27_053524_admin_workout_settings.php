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
        Schema::create(config('tables.admin_workout_plan'), function (Blueprint $table) {
            $table->comment('admin answers saved against questions');

            $table->id();
            $table->integer('workout_program_id')->comment('workout program table id')->nullable();
            $table->unsignedBigInteger('question_id')->comment('Foreign ID of Question');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->text('answer')->nullable();
            $table->timestamps();
            // $table->softDeletes();

            $table->foreign('question_id')->references('id')->on(config('tables.questions'))->onDelete('cascade');
            // $table->foreign('option_id')->references('id')->on(config('tables.question_options'))->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.admin_workout_plan'));
    }
};
