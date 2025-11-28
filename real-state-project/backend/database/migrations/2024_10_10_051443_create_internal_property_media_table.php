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
        Schema::create('internal_property_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('internal_property_id')->references('id')->on('internal_properties')->onDelete('cascade');
            $table->string('path');
            $table->enum('media_type', ['image', 'video']);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_property_media', function (Blueprint $table) {
            $table->dropForeign(['internal_property_id']);
        });
        Schema::dropIfExists('internal_property_media');
    }
};
