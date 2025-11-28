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
        Schema::table('admins', function (Blueprint $table) {
            $table->string('country')->default('South Africa');
            $table->string('timeZone')->default('Africa/Johannesburg');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->string('country')->change();
            $table->string('timeZone')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('country');
            $table->dropColumn('timeZone');
        });
    }
};
