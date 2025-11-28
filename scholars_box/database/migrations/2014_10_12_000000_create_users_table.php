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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('role_id')->nullable()->default(null)->unsigned()->references('id')->on('roles');

            $table->string('first_name')->nullable()->default(null);
            $table->string('last_name')->nullable()->default(null);
            $table->string('social_id')->nullable()->default(null);
            $table->string('social_type')->nullable()->default(null);
            $table->string('email')->nullable()->default(null)->unique();
            $table->string('avatar')->nullable()->default(null);

            $table->string('phone_number')->nullable()->default(null);
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('user_type')->nullable()->default(null);
            $table->string('looking_for')->nullable()->default(null);

            $table->string('whatsapp_number')->nullable()->default(null);
            $table->string('aadhar_card_number')->nullable()->default(null);

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
