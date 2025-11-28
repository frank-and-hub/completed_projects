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
        Schema::create('admin_external_property_users_pivot', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_id')->constrained('admins')->onDelete('cascade');
            $table->foreignUuid('external_property_users_id')
                ->constrained('external_property_users')
                ->onDelete('cascade')
                ->name('admin_external_property_users_pivot_external_property_users_id_fk');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_external_property_users_pivot');
    }
};
