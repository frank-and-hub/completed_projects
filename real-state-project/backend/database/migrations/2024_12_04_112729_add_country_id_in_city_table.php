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
        Schema::table('city', function (Blueprint $table) {
            $table->unsignedMediumInteger('country_id')->default(204);
            // $table->unique(['province_id', 'city_name', 'country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('city', function (Blueprint $table) {

            // $table->dropForeign('city_province_id_foreign');
            // $table->dropUnique('city_province_id_city_name_country_id_unique');
            $table->dropColumn('country_id');
        });
    }
};
