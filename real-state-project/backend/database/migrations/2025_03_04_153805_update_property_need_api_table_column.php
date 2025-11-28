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
        Schema::table('property_needs_apiuser', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('contact')->nullable()->change();
            $table->string('dial_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_needs_apiuser', function (Blueprint $table) {
            //
        });
    }
};
