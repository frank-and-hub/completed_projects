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
        Schema::table('agency_register', function (Blueprint $table) {
            $table->dropColumn('country');
            $table->dropColumn('province');
            $table->dropColumn('city');
        });

        Schema::table('agency_register', function (Blueprint $table) {
            $table->mediumInteger('country')->nullable()->unsigned();
            $table->mediumInteger('province')->nullable()->unsigned();
            $table->mediumInteger('city')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agency_register', function (Blueprint $table) {
            //
        });
    }
};
