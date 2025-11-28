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
            if (Schema::hasColumn('province', 'overpass_id')) {
                $table->dropColumn('overpass_id');
            }
            if (Schema::hasColumn('province', 'lat')) {
                $table->dropColumn('lat');
            }
            if (Schema::hasColumn('province', 'lon')) {
                $table->dropColumn('lon');
            }
        });

        Schema::table('suburb', function (Blueprint $table) {
            if (Schema::hasColumn('suburb', 'elements_tags')) {
                $table->dropColumn('elements_tags');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('province', function (Blueprint $table) {
            if (!Schema::hasColumn('province', 'overpass_id')) {
                $table->string('overpass_id')->nullable();
            }
            if (!Schema::hasColumn('province', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('province', 'lon')) {
                $table->decimal('lon', 10, 7)->nullable();
            }
        });

        Schema::table('suburb', function (Blueprint $table) {
            if (!Schema::hasColumn('suburb', 'elements_tags')) {
                $table->string('elements_tags')->nullable();
            }
        });
    }
};
