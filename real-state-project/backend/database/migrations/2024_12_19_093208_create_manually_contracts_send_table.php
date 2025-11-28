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
        Schema::create('manually_contracts_send', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('contact_no');
            $table->string('country');
            $table->string('phonecode');
            $table->string('email');
            $table->foreignUuid('contract_id')->references('id')->on('contracts')->onDelete('cascade')->comment('contract Id');
            $table->foreignUuid('admin_id')->constrained('id')->on('admins')->onDelete('cascade')->comment('Agent Id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manually_contracts_send');
    }
};
