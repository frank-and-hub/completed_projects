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
        Schema::create(config('tables.user_subscriptions'), function (Blueprint $table) {
            $table->id();
               $table->foreignId('user_id')
                ->constrained('ft_users')
                ->onDelete('cascade')
                ->comment('Foreign key from ft_users');
            $table->unsignedBigInteger('subscription_id')->comment('Foreign ID of Subscription Table');
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->foreign('subscription_id')->references('id')->on('ft_subscription_packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.user_subscriptions'));
    }
};
