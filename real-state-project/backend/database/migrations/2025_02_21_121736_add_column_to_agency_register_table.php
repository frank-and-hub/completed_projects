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
            $table->longText('agency_banner')->nullable()->after('type_of_business');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agency_register', function (Blueprint $table) {
            $table->dropColumn('agency_banner');
        });
    }
};
