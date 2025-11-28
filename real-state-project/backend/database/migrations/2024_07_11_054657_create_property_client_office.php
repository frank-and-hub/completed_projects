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
        Schema::create('property_client_office', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('clientOfficeID');
            $table->string('name');
            $table->string('tel');
            $table->string('fax')->nullable();
            $table->string('email');
            $table->string('website');
            $table->string('logo');
            $table->string('officereference');
            $table->string('sourceId');
            $table->string('profile')->nullable();
            $table->string('physicalAddress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_client_office');
    }
};
