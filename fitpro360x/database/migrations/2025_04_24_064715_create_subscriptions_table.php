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
        Schema::create(config('tables.subscriptions'), function (Blueprint $table) {
            $table->comment('master-subscription plans - workout, gold, platinum');

            $table->id();
            $table->string('name', 100);
            $table->decimal('price', 10, 2);
            $table->enum('duration', ['Monthly', 'Quarterly', 'Yearly'])->default('Monthly');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.subscriptions'));
    }
};
