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
        Schema::create('otp_verify', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('phone')->unique();
            $table->string('otp')->unique();
            $table->timestamp('otp_generated_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verify');
    }
};
