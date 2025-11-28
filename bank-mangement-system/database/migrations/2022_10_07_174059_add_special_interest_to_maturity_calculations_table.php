<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpecialInterestToMaturityCalculationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('maturity_calculations', function (Blueprint $table) {
            $table->decimal('special_interest',30,4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maturity_calculations', function (Blueprint $table) {
            $table->dropColumn('special_interest');
        });
    }
}
