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
        Schema::table('users', function (Blueprint $table) {
            $table->string('timeZone')->default('Africa/Johannesburg')->after('remember_token');
            $table->string('country')->default('South Africa')->after('remember_token');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('country')->nullable()->change();
            $table->string('timeZone')->default('utc')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country');
            $table->dropColumn('timeZone');
        });
    }
};
