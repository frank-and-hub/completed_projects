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
        Schema::create('internal_properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->string('title');
            $table->json('financials');
            $table->json('landSize');
            $table->json('buildingSize');
            $table->string('propertyType');
            $table->string('propertyStatus');
            $table->string('country', 50);
            $table->string('province', 50);
            $table->string('town', 50);
            $table->string('suburb', 50);
            $table->string('lat', 255);
            $table->string('lng', 255);
            $table->json('address');
            $table->boolean('showOnMap')->default(false);
            $table->string('bedrooms');
            $table->string('bathrooms');
            $table->json('location_views')->nullable();
            $table->json('connectivity')->nullable();
            $table->json('outdoor_areas')->nullable();
            $table->json('parking')->nullable();
            $table->json('security_features')->nullable();
            $table->json('energy_efficiency')->nullable();
            $table->json('furnishing')->nullable();
            $table->json('kitchen_features')->nullable();
            $table->json('cooling_heating')->nullable();
            $table->json('laundry_facilities')->nullable();
            $table->json('technology')->nullable();
            $table->json('pet_policy')->nullable();
            $table->json('leisure_amenities')->nullable();
            $table->json('building_features')->nullable();
            $table->json('flooring')->nullable();
            $table->json('proximity')->nullable();
            $table->json('storage_space')->nullable();
            $table->json('communal_areas')->nullable();
            $table->json('maintenance_services')->nullable();
            $table->json('water_features')->nullable();
            $table->json('entertainment')->nullable();
            $table->json('accessibility')->nullable();
            $table->json('lease_options')->nullable();
            $table->json('location_features')->nullable();
            $table->json('noise_control_features')->nullable();
            $table->json('fire_safety_features')->nullable();
            $table->text('description')->nullable();
            $table->string('action')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('internal_properties', function (Blueprint $table) {
            // $table->dropForeign(['admin_id']);
        // });
        Schema::dropIfExists('internal_properties');
    }
};
