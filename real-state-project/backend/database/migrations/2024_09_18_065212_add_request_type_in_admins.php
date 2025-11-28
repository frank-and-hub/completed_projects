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
            // $table->enum('request_type', ['pending', 'cancelled', 'accepted'])->after('status')->default('pending');
            $table->string('password_text')->after('password')->nullable();
            $table->string('dial_code')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // $table->dropColumn('request_type');
            $table->dropColumn('password_text');
            $table->dropColumn('dial_code');
        });
    }
};
