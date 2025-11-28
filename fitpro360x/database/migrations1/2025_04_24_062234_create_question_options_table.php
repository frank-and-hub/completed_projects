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
        Schema::create(config('tables.question_options'), function (Blueprint $table) {
            $table->comment('Options for each questions');

            $table->id();
            $table->unsignedBigInteger('question_id')->comment('Foreign ID of Questions');
            $table->string('label')->nullable();
            $table->string('instruction')->nullable()->comment('Comment or instruction if any');
            $table->string('value')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('question_id')->references('id')->on(config('tables.questions'))->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.question_options'));
    }
};
