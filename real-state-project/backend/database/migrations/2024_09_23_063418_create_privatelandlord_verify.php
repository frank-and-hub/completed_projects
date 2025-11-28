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
        Schema::create('privatelandlord_verify', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->string('phone_otp')->nullable();
            $table->string('email_otp')->nullable();
            $table->timestamp('phone_otp_generated_at')->nullable();
            $table->timestamp('email_otp_generated_at')->nullable();
            $table->timestamp('phone_otp_verified_at')->nullable();
            $table->timestamp('email_otp_verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privatelandlord_verify');
    }
};
