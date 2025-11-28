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
        Schema::create(config('tables.exercises'), function (Blueprint $table) {
            $table->id();
            $table->string('exercise_name', 255)->nullable();
            $table->tinyInteger('level')->nullable()->comment('1=>Beginner, 2=>Intermediate, 3=>Advance');
            $table->tinyInteger('location')->default(1)->comment('1=>Home, 2=>Gym');
            $table->text('equipment')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('description', 1200)->nullable();
            $table->string('video', 255)->nullable();
            $table->integer('muscles_trained_id')->nullable();
            $table->unsignedBigInteger('body_type_id');
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('muscles_trained_id')->references('id')->on(config('tables.muscle_trained'))->onDelete('cascade');
            $table->foreign('body_type_id')->references('id')->on(config('tables.body_type'))->onDelete('cascade'); // Added onDelete('cascade')

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.exercises'));
    }
};
