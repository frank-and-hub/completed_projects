<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTdsAmountToMemberInvestmentInterestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_investment_interest', function (Blueprint $table) {
            $table->bigInteger('tds_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_investment_interest', function (Blueprint $table) {
            $table->bigInteger('tds_amount');
        });
    }
}
