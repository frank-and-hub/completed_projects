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
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('clientPropertyID');
            $table->string('trID');
            $table->string('currency', 3);
            $table->decimal('price', 15, 2);
            $table->decimal('ratesAndTaxes', 15, 2)->default(0);
            $table->decimal('levy', 15, 2)->default(0);
            $table->decimal('landSize', 15, 2)->default(0);
            $table->string('landsizeType', 10)->default('m²');
            $table->decimal('buildingSize', 15, 2)->default(0);
            $table->string('buildingSizeType', 10)->default('m²');
            $table->string('propertyType', 50);
            $table->string('propertyStatus', 50);
            $table->string('country', 50);
            $table->string('province', 50);
            $table->string('town', 50);
            $table->string('suburb', 50);
            $table->decimal('beds', 3, 1);
            $table->text('bedroomFeatures')->nullable();
            $table->decimal('baths', 3, 1);
            $table->text('bathroomFeatures')->nullable();
            $table->boolean('pool')->default(false);
            $table->date('listDate')->nullable();
            $table->date('expiryDate')->nullable();
            $table->date('occupationDate')->nullable();
            $table->integer('study')->default(0);
            $table->integer('livingAreas')->default(0);
            $table->integer('staffAccommodation')->default(0);
            $table->integer('carports')->default(0);
            $table->integer('garages')->default(0);
            $table->boolean('petsAllowed')->default(false);
            $table->text('description')->nullable();
            $table->string('propertyFeatures', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('priceUnit', 50)->default('total');
            $table->boolean('isReduced')->default(false);
            $table->boolean('isDevelopment')->default(false);
            $table->string('mandate', 50)->default('Open');
            $table->boolean('furnished')->default(false);
            $table->integer('openparking')->default(0);
            $table->string('streetNumber', 50)->nullable();
            $table->string('streetName', 255)->nullable();
            $table->string('unitNumber', 50)->nullable();
            $table->string('complexName', 255)->nullable();
            $table->string('latlng', 255)->nullable();
            $table->boolean('showOnMap')->default(false);
            $table->string('action', 50)->default('create');
            $table->string('vtUrl', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
