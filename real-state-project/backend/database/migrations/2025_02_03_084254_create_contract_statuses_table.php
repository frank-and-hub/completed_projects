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
        Schema::create('contract_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade')->comment('tenant Id');
            $table->foreignUuid('admin_id')->constrained('admins')->on('admins')->onDelete('cascade')->comment('Admin,Agency,LandLoad');
            $table->foreignUuid('contract_id')->references('id')->on('contracts')->onDelete('cascade')->comment('contract Id');
            $table->longtext('contract_path');
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_statuses');
    }
};
