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
        Schema::create(config('tables.user_challenge_progress'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('week');
            $table->unsignedBigInteger('day_id');
            $table->unsignedBigInteger('challenge_id');
            $table->tinyInteger('status')->default(0)->comment('0 = false, 1 = partial, 2 = completed');
            $table->tinyInteger('is_active')->default(1)->comment('0 = inactive, 1 = active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.user_challenge_progress'));
    }
};
