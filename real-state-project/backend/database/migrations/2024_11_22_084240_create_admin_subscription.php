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
        Schema::create('admin_subscription', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('admin_id');
            $table->string('subscription_id');
            $table->string('plan_name');
            $table->string('amount');
            $table->enum('status', ['pending', 'ongoing', 'cancelled', 'expired']);
            $table->integer('total_property')->default(0);
            $table->integer('can_add_property')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_subscription');
    }
};
