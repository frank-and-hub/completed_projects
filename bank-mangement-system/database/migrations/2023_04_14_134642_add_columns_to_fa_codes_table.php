<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToFaCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fa_codes', function (Blueprint $table) {
            $table->tinyInteger('is_required')->comment('1:yes; 0:no')->default('0');
            $table->string('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fa_codes', function (Blueprint $table) {
            $table->tinyInteger('is_required')->comment('1:yes; 0:no')->default('0');
            $table->string('slug');
        });
    }
}
