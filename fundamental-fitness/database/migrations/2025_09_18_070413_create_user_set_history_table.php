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
        Schema::dropIfExists(config('tables.user_set_history'));
        Schema::create(config('tables.user_set_history'), function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->integer('user_id')->comment('Foreign ID of Users Table');
            $table->integer('meso_id')->nullable();
            $table->integer('set_id')->nullable();
            $table->integer('week_id')->nullable();
            $table->integer('day_id')->nullable();
            $table->integer('exercise_id')->nullable();
            $table->decimal('weight', 8, 2)->comment('Weight lifted');
            $table->integer('reps')->comment('Reps performed');
            $table->decimal('rpe', 3, 1)->comment('Rate of Perceived Exertion (1-10)');
            $table->tinyInteger('status')->default(1)->comment('0 = false, 1 = partial, 2= completed');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.user_set_history'));
    }
};
