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
        Schema::table('province', function (Blueprint $table) {
            $table->unsignedMediumInteger('country_id')->default(204)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('province', function (Blueprint $table) {
            // $table->dropForeign('province_country_id_foreign');
            $table->dropColumn('country_id');
        });
    }
};
