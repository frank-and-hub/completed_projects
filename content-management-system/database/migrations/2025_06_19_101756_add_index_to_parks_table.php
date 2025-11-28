<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parks', function (Blueprint $table) {
            $table->index(['city', 'state', 'country'], 'idx_parks_city_state_country');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->index(['city', 'state', 'country'], 'idx_locations_city_state_country');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parks', function (Blueprint $table) {
            $table->dropIndex('idx_parks_city_state_country');
        });

        Schema::table('locations', function (Blueprint $table) {
             $table->dropIndex(['status']);
             $table->dropIndex('idx_locations_city_state_country');
        });
    }
};
