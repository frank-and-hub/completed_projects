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
        Schema::dropIfExists(config('tables.watched_videos'));
        Schema::create(config('tables.watched_videos'), function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('exercise_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('video_count')->default(0);
            $table->timestamps();

            $table->foreign('exercise_id')
                ->references('id')
                ->on(config('tables.exercises'))
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on(config('tables.users'))
                ->onDelete('cascade');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.watched_videos'));
    }
};
