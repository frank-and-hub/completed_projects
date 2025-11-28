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
        Schema::create(config('tables.question_answers_user'), function (Blueprint $table) {
            $table->comment('user answers saved against questions');

            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Foreign ID of Users');
            $table->unsignedBigInteger('question_id')->comment('Foreign ID of Question');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->text('answer')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on(config('tables.users'))->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on(config('tables.questions'))->onDelete('cascade');
            // $table->foreign('option_id')->references('id')->on(config('tables.question_options'))->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.question_answers_user'));
    }
};
