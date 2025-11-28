<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feature_types', function (Blueprint $table) {
            $table->enum('type',['normal','popular'])->default('normal')->after('name');
        });
    }

    public function down()
    {
        Schema::table('feature_types', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
