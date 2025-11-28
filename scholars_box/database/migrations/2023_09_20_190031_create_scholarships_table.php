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
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name_of_csr')->nullable();
            $table->string('scholarship_title')->nullable();
            $table->string('year')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('scholarship_contact_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('csr_id')->constrained('scholarships')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('email_id')->nullable();
            $table->timestamps();
        });

        Schema::create('scholarship_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('csr_id')->constrained('scholarships')->onDelete('cascade');
            $table->string('country_id')->nullable();
            $table->string('state_id')->nullable();
            $table->string('district')->nullable();
            $table->timestamps();
        });

        Schema::create('scholarship_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('csr_id')->constrained('scholarships')->onDelete('cascade');
            $table->string('education_level')->nullable();
            $table->timestamps();
        });

        Schema::create('scholarship_scholarship_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('csr_id')->constrained('scholarships')->onDelete('cascade');
            $table->text('about_scholarship')->nullable();
            $table->text('brief_about_scholarship')->nullable();
            $table->text('brief_about_csr')->nullable();
            $table->string('scholarship_amount')->nullable();
            $table->text('eligibility_criteria')->nullable();
            $table->text('document_required')->nullable();
            $table->string('queries_contact')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('scholarship_education');
        Schema::dropIfExists('scholarship_locations');
        Schema::dropIfExists('scholarship_contact_persons');
        Schema::dropIfExists('scholarship_csr_information');
    }
};
