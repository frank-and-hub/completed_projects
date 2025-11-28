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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Personal Information
            $table->boolean('is_minority')->default(false);
            $table->string('minority_group')->nullable()->default(null);
            $table->string('category')->nullable()->default(null);
            $table->string('other_reservation')->nullable()->default(null);
            $table->boolean('is_pwd_category')->default(false);
            $table->integer('pwd_percentage')->nullable()->default(null);
            $table->boolean('is_army_veteran_category')->default(false);
            $table->string('army_veteran_data')->default(false);

            // Occupation
            $table->string('occupation')->nullable()->default(null);
            $table->string('is_pm_same_as_current')->nullable()->default(null);
            $table->string('current_citizenship')->nullable()->default(null);


            $table->timestamps();
        });

        Schema::create('education_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('institute_name')->nullable()->default(null);
            $table->string('institute_type')->nullable()->default(null);
            $table->string('district')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('course_name')->nullable()->default(null);
            $table->string('specialisation')->nullable()->default(null);
            $table->string('grade_type')->nullable()->default(null);
            $table->string('grade')->nullable()->default(null);
            $table->date('start_date')->nullable()->default(null);
            $table->date('end_date')->nullable()->default(null);
            $table->enum('level', ['highschool', 'intermediate', 'graduation'])->nullable()->default(null);
            $table->string('pursuing')->nullable()->default(null);

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('employment_type')->nullable()->default(null);
            $table->string('company_name')->nullable()->default(null);
            $table->string('designation')->nullable()->default(null);
            $table->date('joining_date')->nullable()->default(null);
            $table->date('end_date')->nullable()->default(null);
            $table->string('job_role')->nullable()->default(null);

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('guardian_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('name')->nullable()->default(null);
            $table->string('relationship')->nullable()->default(null);
            $table->string('occupation')->nullable()->default(null);
            $table->string('phone_number')->nullable()->default(null);
            $table->string('number_of_siblings')->nullable()->default(null);
            $table->integer('annual_income')->nullable()->default(null);

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('address_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('house_type')->nullable()->default(null);
            $table->string('address')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('district')->nullable()->default(null);
            $table->string('pincode')->nullable()->default(null);
            $table->enum('type', ['current', 'permanent']); // Added the address type field

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('document_type')->nullable()->default(null);
            $table->string('document')->nullable()->default(null);

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('address_details');
        Schema::dropIfExists('guardian_details');
        Schema::dropIfExists('employment_details');
        Schema::dropIfExists('education_details');
        Schema::dropIfExists('students');
    }
};
