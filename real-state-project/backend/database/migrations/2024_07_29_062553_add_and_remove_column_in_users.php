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
            if (Schema::hasColumn('users', 'google_id')) {
                $table->dropColumn('google_id');
            }
            if (Schema::hasColumn('users', 'outlook_id')) {
                $table->dropColumn('outlook_id');
            }
            $table->enum('social_type', ['google', 'microsoft'])->nullable()->after('type');
            $table->string('social_id')->nullable()->after('social_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('social_type');
            $table->dropColumn('social_id');
        });
    }
};
