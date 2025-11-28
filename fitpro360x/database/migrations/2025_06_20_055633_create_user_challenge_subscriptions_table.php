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
          Schema::create('ft_user_challenge_subscription', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('Foreign ID of Users Table');
            $table->unsignedBigInteger('subscription_id')->comment('Gateway subscription ID');
            $table->text('transaction_id');
            $table->enum('payment_gateway', ['android', 'ios'])->nullable()->comment('payment_gateway');
            $table->timestamp('subscribed_at')->comment('Subscription start');
            $table->tinyInteger('is_recurring')->comment('Is it a recurring subscription');
            $table->enum('status', ['pending', 'active', 'cancelled', 'expired'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key Constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('ft_challenge_packages')->onDelete('cascade');
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ft_user_challenge_subscription');
    }
};
