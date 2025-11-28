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
        Schema::create('suburb', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('province_id')->references('id')->on('province')->onDelete('cascade');
            $table->foreignUuid('city_id')->nullable()->references('id')->on('city')->onDelete('cascade');
            $table->string('suburb_name');
            $table->text('elements_tags');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suburb');
    }
};
