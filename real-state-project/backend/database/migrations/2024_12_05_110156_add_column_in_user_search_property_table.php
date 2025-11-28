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
            $table->string('currency')->default('ZAR');
            $table->string('currency_name')->default('South African rand');
            $table->string('currency_symbol')->default('R');
        });

        Schema::table('user_search_property', function (Blueprint $table) {
            $table->string('currency')->change();
            $table->string('currency_name')->change();
            $table->string('currency_symbol')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_search_property', function (Blueprint $table) {
            $table->dropColumn('currency');
            $table->dropColumn('currency_name');
            $table->dropColumn('currency_symbol');
        });
    }
};
