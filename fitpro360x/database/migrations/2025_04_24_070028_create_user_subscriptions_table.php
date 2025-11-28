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
        Schema::create('user_subscriptions', function (Blueprint $table) {


            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id');
            $table->string('subscription_id')->nullable(); // Gateway subscription ID
            $table->string('transaction_id')->nullable(); // Payment transaction ID
            $table->string('originalTransactionId')->nullable(); // For Apple/Google subscriptions
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('payment_gateway')->nullable(); // e.g. stripe, razorpay, apple, google

            $table->timestamp('expires_at')->nullable(); // Subscription end
            $table->timestamp('subscribed_at')->nullable(); // Subscription start
            $table->boolean('is_recurring')->default(true); // Recurring flag
            $table->tinyInteger('status')->default(1); // 1: active, 0: cancelled/expired
            $table->boolean('is_expire_email_sent')->default(false); // Email sent flag

            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('ft_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
