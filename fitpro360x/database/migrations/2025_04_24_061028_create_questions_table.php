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
            $table->bigIncrements('id');
            $table->string('title_for_web')->nullable();
            $table->string('title_for_app')->nullable();
            $table->string('sub_title_for_app')->nullable()->comment('Sub Title of questions');

            $table->tinyInteger('type_for_app')->comment('1 => single_choice, 2 => multiple_choice, 3 => text, 4 => slider, 5 => info');
            $table->tinyInteger('type_for_web')->nullable()->comment('1 => single_choice, 2 => multiple_choice, 3 => text, 4 => slider, 5 => info');
            $table->tinyInteger('showing_in')->comment('1 => Web, 2 => App, 3 => Both');

            $table->integer('question_order_for_web')->nullable();
            $table->integer('question_order_for_app')->nullable();

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
