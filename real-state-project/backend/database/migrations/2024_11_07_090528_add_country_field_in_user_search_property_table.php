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
            $table->string('country')->default('South Africa')->after('user_subscription_id');
        });

        Schema::table('user_search_property', function (Blueprint $table) {
            $table->string('country')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_search_property', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
};
