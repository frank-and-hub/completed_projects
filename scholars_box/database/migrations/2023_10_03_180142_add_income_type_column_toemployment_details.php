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
        if ( !Schema::hasColumn('income_type','guardian_details')) {
            Schema::table('guardian_details', function (Blueprint $table) {
                $table->string('income_type')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ( Schema::hasColumn('income_type','guardian_details')) {
            Schema::table('guardian_details', function (Blueprint $table) {
                $table->dropColumn('income_type');
            });
        }
    }
};
