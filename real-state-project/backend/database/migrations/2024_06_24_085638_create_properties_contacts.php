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
        Schema::create('properties_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('properties_id')->references('id')->on('properties')->onDelete('cascade');
            $table->string('clientPropertyID');
            $table->integer('clientOfficeID');
            $table->string('officeName');
            $table->string('officeTel', 50);
            $table->string('officeFax', 50)->nullable();
            $table->string('officeEmail', 255);
            $table->integer('clientAgentID');
            $table->string('fullName');
            $table->string('cell', 50);
            $table->string('email', 255);   
            $table->text('profile')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties_contacts');
    }
};
