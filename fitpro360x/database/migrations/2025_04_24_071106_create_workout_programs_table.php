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
        Schema::create(config('tables.workout_programs'), function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->comment('Program title');
            $table->string('goal', 100)->nullable()->comment('Program goal');
            $table->tinyInteger('location')->default(1)->comment('1=>Home, 2=>Gym');
            $table->integer('duration_weeks')->nullable()->comment('Duration weeks');
            $table->string('level', 50)->nullable()->comment('Skill level');
            $table->text('description')->nullable()->comment('Program description');
            $table->string('image')->nullable(); // 'image' column, varchar(255), nullable
            $table->tinyInteger('status')->default(1)->comment('0 = Inactive, 1 = Active');
            $table->timestamps(); // Created & updated timestamps
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.workout_programs'));
    }
};
