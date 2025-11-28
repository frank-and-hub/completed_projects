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
        Schema::create('scholarship_application_details', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('application_id')->nullable()->default(null)->references('id')->on('scholarship_applications')->onDelete('cascade');

            $table->string('mother_tongue')->nullable()->default(null);
            $table->boolean('disability')->default(false);
            $table->string('account_holder_name')->nullable()->default(null);
            $table->string('account_number')->nullable()->default(null);
            $table->string('bank_name')->nullable()->default(null);
            $table->string('branch')->nullable()->default(null);
            $table->string('ifsc')->nullable()->default(null);

            $table->timestamps();
        });
    }

    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_application_details');
    }
};
