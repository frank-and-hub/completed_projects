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
        Schema::create('credit_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->references('id')->onDelete('cascade')->comment('User');
            $table->string('credit_report_pdf');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone_number');
            $table->timestamp('report_date');
            $table->json('data');
            $table->timestamp('date_of_birth');
            $table->string('identity_number');
            $table->string('marital_status');
            $table->string('signature');
            $table->json('documents_identity_document');
            $table->json('documents_photo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_reports');
    }
};
