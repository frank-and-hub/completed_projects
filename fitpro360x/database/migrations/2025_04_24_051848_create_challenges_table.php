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
        Schema::create(config('tables.challenges'), function (Blueprint $table) {
            $table->comment('main table for challenges');


            $table->id();
            $table->string('title',255)->comment('Title of the course');
            $table->text('description')->comment('Detailed description of the course');
            $table->integer('duration_weeks')->comment('Course duration in weeks');
            $table->string('image',255)->comment('Path or URL to course image');
            $table->decimal('price', 10, 2)->comment('Course price in decimal format');
            $table->enum('level', ['Beginner', 'Intermediate', 'Advanced'])->comment('Difficulty level of the course');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.challenges'));
    }
};

