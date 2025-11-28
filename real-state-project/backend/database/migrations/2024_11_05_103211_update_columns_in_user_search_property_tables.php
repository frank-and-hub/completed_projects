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
        Schema::table('user_search_property', function (Blueprint $table) {
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_search_property', function (Blueprint $table) {
            $table->dropColumn('location_views');
            $table->dropColumn('connectivity');
            $table->dropColumn('outdoor_areas');
            $table->dropColumn('parking');
            $table->dropColumn('security_features');
            $table->dropColumn('energy_efficiency');
            $table->dropColumn('furnishing');
            $table->dropColumn('kitchen_features');
            $table->dropColumn('cooling_heating');
            $table->dropColumn('laundry_facilities');
            $table->dropColumn('technology');
            $table->dropColumn('pet_policy');
            $table->dropColumn('leisure_amenities');
            $table->dropColumn('building_features');
            $table->dropColumn('flooring');
            $table->dropColumn('proximity');
            $table->dropColumn('storage_space');
            $table->dropColumn('communal_areas');
            $table->dropColumn('maintenance_services');
            $table->dropColumn('water_features');
            $table->dropColumn('entertainment');
            $table->dropColumn('accessibility');
            $table->dropColumn('lease_options');
            $table->dropColumn('location_features');
            $table->dropColumn('noise_control_features');
            $table->dropColumn('fire_safety_features');
        });
    }
};
