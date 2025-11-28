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
        Schema::table('education_details', function (Blueprint $table) {
            DB::statement('ALTER TABLE education_details MODIFY level VARCHAR(255) NULL DEFAULT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_details', function (Blueprint $table) {
            DB::statement('ALTER TABLE education_details MODIFY level ENUM("highschool", "intermediate", "graduation") NULL DEFAULT NULL');
        });
    }
};
