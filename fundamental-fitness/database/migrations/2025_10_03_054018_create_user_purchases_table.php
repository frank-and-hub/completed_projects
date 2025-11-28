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
        Schema::dropIfExists(config('tables.purchases'));
        Schema::create(config('tables.purchases'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('product_id');
            $table->enum('platform', ['ios', 'android'])->default('android');
            $table->longText('purchase_token');
            $table->decimal('amount', 8, 2)->default(0);
            $table->longText('transaction_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=>pending,2=>active,3=>expired,4=>cancelled');
            $table->date('transaction_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.purchases'));
    }
};
