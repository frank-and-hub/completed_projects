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
        Schema::create(config('tables.workouts'), function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('workout_frequency_id')->nullable();
            $table->unsignedBigInteger('meso_id')->nullable();
            $table->integer('week_id')->nullable();
            $table->integer('day_id')->nullable();
            $table->unsignedBigInteger('exercise_id');
            $table->tinyInteger('level')->default(1)->comment('1=>Beginner, 2 =>Intermediate, 3=> advanced');
            $table->string('image', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->string('gif', 255)->nullable();
            $table->longText('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
            $table->foreign('workout_frequency_id')
                ->references('id')
                ->on(config('tables.workout_frequencies'))
                ->onDelete('cascade');
            $table->foreign('meso_id')
            ->references('id')
            ->on(config('tables.meso_cycles'))
            ->onDelete('cascade');
            $table->foreign('exercise_id')
                ->references('id')
                ->on(config('tables.exercises'))
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('tables.workouts'), function (Blueprint $table) {
            $table->dropForeign(['workout_frequency_id']);
            $table->dropForeign(['exercise_id']);
        });

        Schema::dropIfExists(config('tables.workouts'));
    }
};
