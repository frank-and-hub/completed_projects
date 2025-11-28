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
        Schema::create('agency_register', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->string('f_name');
            $table->string('l_name');
            $table->string('business_name');
            $table->string('id_number');
            $table->string('registration_number');
            $table->string('vat_number')->nullable();
            $table->string('street_address')->nullable();
            $table->string('street_address_2')->nullable();
            $table->string('postal_code');
            $table->string('type_of_business');
            $table->string('message')->nullable();

            $table->string('country');
            $table->string('province');
            $table->string('city');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_register');
    }
};
