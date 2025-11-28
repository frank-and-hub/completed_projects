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
        Schema::create(config('tables.workout_sets'), function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('workout_id');
            $table->integer('set_number')->comment("For running 1 = Duration, 2 = Distance \r\nother are integer value");
            $table->string('reps', 255)->nullable()->comment('min 1 to 10 max');
            $table->string('reps_unit', 10)->nullable()->comment("Only For running\r\n1=>min, 2=>km");
            $table->string('rpe', 255)->nullable()->comment('Rate of Perceived Exertion ort in percentage');
            $table->string('rpe_percentage', 255)->nullable()->comment('1 - 10 and extra other');
            $table->integer('rest')->nullable();
            $table->enum('rest_unit', ['seconds', 'minutes'])->default('seconds')->comment('Rest time unit');

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
        Schema::dropIfExists(config('tables.workout_sets'));
    }
};
