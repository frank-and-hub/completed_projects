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
        Schema::create(config('tables.challenge_weeks'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger('challenge_id')->unsigned()->comment('Challenge reference');
            $table->unsignedInteger('week_number')->comment('Week number');
            $table->unsignedInteger('day_number')->comment('Day number');
            $table->boolean('is_rest_day')->default(false)->nullable();

            // Add foreign key constraint if needed
            // $table->foreign('challenge_id')->references('id')->on('ft_challenges');
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
        Schema::dropIfExists(config('tables.challenge_weeks'));
    }
};
