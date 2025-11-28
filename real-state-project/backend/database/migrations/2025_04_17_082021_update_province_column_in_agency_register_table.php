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
            // Check if the 'country' column exists, if not, add it
            if (!Schema::hasColumn('agency_register', 'country')) {
                $table->char('country', 36)->nullable();
            }

            // Check if the 'province' column exists, if not, add it
            if (!Schema::hasColumn('agency_register', 'province')) {
                $table->char('province', 36)->nullable();
            }

            // Check if the 'city' column exists, if not, add it
            if (!Schema::hasColumn('agency_register', 'city')) {
                $table->char('city', 36)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agency_register', function (Blueprint $table) {
            if (Schema::hasColumn('agency_register', 'country')) {
                $table->dropColumn('country');
            }

            // Drop 'province' column if it exists
            if (Schema::hasColumn('agency_register', 'province')) {
                $table->dropColumn('province');
            }

            // Drop 'city' column if it exists
            if (Schema::hasColumn('agency_register', 'city')) {
                $table->dropColumn('city');
            }
        });
    }
};
