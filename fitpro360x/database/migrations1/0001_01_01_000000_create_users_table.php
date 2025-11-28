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


        Schema::create(config('tables.users'), function (Blueprint $table) {
            $table->id();
            $table->string('fullname', 255)->comment('Full name of the user');
            $table->string('email', 255)->unique()->comment('User email address');
            $table->string('password', 255)->comment('User password');
            $table->string('profile_photo', 255)->nullable()->comment('Profile photo path');
            $table->string('device_type', 255)->nullable()->comment('Device type of the user');
            $table->string('device_id', 255)->nullable()->comment('Device ID for user device tracking');
            $table->string('forgot_token', 255)->nullable()->comment('Password reset token');
            $table->unsignedBigInteger('role')->default(2)->comment('1 = Admin, 2 = Users');
            $table->tinyInteger('language')->default(1)->comment('1 = English, 2 = Hindi');
            $table->tinyInteger('status')->default(1)->comment('0 = Inactive, 1 = Active');
            $table->boolean('notifications_enabled')->default(1)->comment('0 = Notifications Disabled, 1 = Notifications Enabled');
            //is_profile_completed
            $table->boolean('is_profile_completed')->default(0)->comment('0 = Incomplete, 1 = Complete');
            $table->integer('last_token_id')->nullable()->comment('Last token ID for tracking purposes');
            $table->timestamp('created_at')->useCurrent()->comment('Record creation timestamp');
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->comment('Record update timestamp');
            $table->softDeletes()->comment('Soft delete timestamp');

        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.users'));
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
