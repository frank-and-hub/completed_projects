<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('property_needs_apiuser', function (Blueprint $table) {
            $table->string('user_name')->nullable()->change();
            $table->foreignUuid('admin_id')->constrained('admins')->onDelete('cascade')->comment('Agency');
            $table->string('suburb_name')->nullable();
            $table->string('city')->nullable();
            $table->string('property_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_needs_apiuser', function (Blueprint $table) {
            $table->dropForeign('property_needs_apiuser_admin_id_foreign');
            $table->dropColumn('admin_id');
            $table->dropColumn('suburb_name');
            $table->dropColumn('city');
            $table->dropColumn('property_type');
        });
    }
};
