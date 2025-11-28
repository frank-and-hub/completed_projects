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
        Schema::table('sent_internal_property_users', function (Blueprint $table) {
            $table->enum('credit_reports_status', ['approved', 'unapproved'])->default('unapproved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sent_internal_property_users', function (Blueprint $table) {
            $table->dropColumn('credit_reports_status');
        });
    }
};
