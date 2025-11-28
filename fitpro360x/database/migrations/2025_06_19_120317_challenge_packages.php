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
       Schema::create('ft_challenge_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('plan_name');
            $table->tinyInteger('type')->nullable()->default(2)->comment('1 => Monthly, 2 => Yearly');
            $table->integer('duration')->nullable()->comment('Number of months');
            $table->integer('amount')->nullable();
            $table->text('description')->nullable();
            $table->text('product_id');
            $table->tinyInteger('active');
            $table->tinyInteger('status');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            // $table->engine = 'InnoDB'; // Ensure foreign keys work
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('ft_challenge_packages');
    }
};
