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
        Schema::create('property_time_slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreignUuid('property_id')->nullable();
            $table->foreignUuid('internal_property_id')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('start_day_of_week', nameOfWeeks());
            $table->enum('end_day_of_week', nameOfWeeks());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_time_slots');
    }
};
