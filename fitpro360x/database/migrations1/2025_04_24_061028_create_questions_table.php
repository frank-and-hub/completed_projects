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
        Schema::create(config('tables.questions'), function (Blueprint $table) {
            $table->comment('Master Table');

            $table->id();
            $table->string('title');
            $table->string('sub_title')->nullable()->comment('Sub Title of questions');
            $table->enum('type', ['single_choice', 'multiple_choice', 'text','slider','info'])->comment('Types of Fields');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.questions'));
    }
};
