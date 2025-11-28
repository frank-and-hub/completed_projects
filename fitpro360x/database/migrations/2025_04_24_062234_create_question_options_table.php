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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('question_id')->comment('ft Questions PK');

            $table->string('label_for_app')->nullable();
            $table->string('label_for_web')->nullable();
            $table->string('instruction')->nullable()->comment('Comment or instruction if any');
            $table->string('value')->nullable();

            $table->integer('min_val')->nullable();
            $table->integer('max_val')->nullable();

            $table->string('image')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreign('question_id')->references('id')->on(config('tables.questions'))->onDelete('cascade');


            // Optional foreign key constraint
            // $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
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
