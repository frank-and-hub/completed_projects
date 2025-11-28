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
        Schema::create('user_search_property', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('province_name');
            $table->string('suburb_name');
            $table->string('city');
            $table->string('property_type');
            $table->string('start_price');
            $table->string('end_price');
            $table->string('no_of_bedroom');
            $table->string('no_of_bathroom');
            $table->string('additional_features')->nullable();
            $table->boolean('send_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_search_property');
    }
};
