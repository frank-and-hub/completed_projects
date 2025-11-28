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
        Schema::create(config('tables.challenge_program'), function (Blueprint $table) {
            $table->id();
            $table->string('title'); // 'title' varchar(255), not null
            $table->string('goal', 100)->nullable(); // 'goal' varchar(100), nullable
            $table->integer('duration_weeks')->nullable(); // 'duration_weeks' int, nullable
            $table->string('level', 50)->nullable(); // 'level' varchar(50), nullable
            $table->text('description')->nullable(); // 'description' text, nullable
            $table->string('image')->nullable(); // 'image' varchar(255), nullable
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.challenge_program'));
    }
};
