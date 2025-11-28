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
            $table->foreignUuid('user_subscription_id')->after('user_id')->references('id')->on('user_subscription')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_search_property', function (Blueprint $table) {
            $table->dropForeign(['user_subscription_id']);
            // $table->dropColumn('user_subscription_id');
        });
    }
};
