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
        Schema::create(config('tables.subscription_packages'), function (Blueprint $table) {
                $table->id();
                $table->string('plan_name', 255);
                $table->tinyInteger('type')->default(1)->comment('1 => Monthly, 2 => Yearly');
                $table->integer('duration')->default(1)->comment('Number of months');
                $table->integer('amount');
                $table->text('description')->nullable();
                $table->boolean('active')->default(1)->comment('1 => Active, 0 => Inactive');
                $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive');
                $table->string('product_id', 255)->unique()->comment('Product ID for in-app purchases');
                $table->timestamps();
                $table->softDeletes();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.subscription_packages'));
    }
};
