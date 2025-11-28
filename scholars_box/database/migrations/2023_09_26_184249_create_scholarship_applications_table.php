<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scholarship_applications', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('scholarship_id')->nullable()->default(null)->references('id')->on('scholarships')->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->nullable()->default(null)->references('id')->on('users')->onDelete('cascade');

            $table->string('status')->nullable()->default(null);

            $table->timestamp('applied_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_applications');
    }
};
