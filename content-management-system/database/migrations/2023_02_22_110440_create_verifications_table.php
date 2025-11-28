<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('scope');
            $table->string("verification_type")->comment("email or phone");
            $table->string("verifying")->comment("value email or phone");
            $table->string('link', 4096)->nullable();
            $table->string('otp', 16)->nullable();
            $table->dateTime('valid_upto');
            $table->enum('status', ['pending', 'used'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('verifications');
    }
};
