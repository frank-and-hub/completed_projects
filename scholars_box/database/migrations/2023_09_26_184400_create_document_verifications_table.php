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
        Schema::create('document_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('application_id')->nullable()->default(null)->references('id')->on('scholarship_applications')->onDelete('cascade');

            $table->string('document_type')->nullable()->default(null);
            $table->string('document')->nullable()->default(null);

            $table->unsignedBigInteger('verified_by_id')->nullable()->default(null)->references('id')->on('users')->onDelete('cascade');

            $table->dateTime('verified_on')->nullable()->default(null);
            $table->string('status')->nullable()->default(null); // ['Verified', 'Incorrect', 'Missing', 'Blurred', 'Ineligible']

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
