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
        Schema::create(config('tables.user_workouts'), function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('workout_frequency_id', 255);
            $table->string('meso_id', 255);
            $table->integer('day_id');
            $table->integer('week_id');
            $table->unsignedBigInteger('exercise_id');
            $table->tinyInteger('level')->default(1)->comment('1=>Beginner, 2 =>Intermediate, 3=> advanced');
            $table->string('image', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->string('gif', 255)->nullable();
            $table->longText('description')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on(config('tables.users'))
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
        Schema::dropIfExists(config('tables.user_workouts'));
    }
};
