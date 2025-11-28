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
        Schema::create('application_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('csr_id')->constrained('scholarships')->onDelete('cascade');
            $table->string('name')->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('eligibility_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_form_id')->constrained('application_forms');
            $table->string('section')->nullable()->default(null);
            $table->text('title')->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eligibility_check_id')->constrained('eligibility_checks');
            $table->string('question_type')->nullable()->default(null);
            $table->text('question_text')->nullable()->default(null);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('eligibility_checks');
        Schema::dropIfExists('questions');
    }
};
