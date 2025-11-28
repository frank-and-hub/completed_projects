<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFitnessChallengesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('tables.fitness_challenges'), function (Blueprint $table) {
           $table->id();
            $table->string('challenge_name');
            $table->string('goal');
            $table->integer('duration_weeks');
            $table->unsignedBigInteger('plan_id');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('plan_id')
                  ->references('id')
                  ->on('ft_challenge_packages')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fitness_challenges');
    }
}
