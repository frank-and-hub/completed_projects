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
        Schema::create('properties_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('properties_id')->references('id')->on('properties')->onDelete('cascade');
            $table->string('clientPropertyID');
            $table->string('imgUrl');
            $table->text('imgDescription')->nullable();
            $table->boolean('isMain')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties_images');
    }
};
