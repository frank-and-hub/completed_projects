<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaturityAmountInterestRateIntoMemberInvestment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('member_investments', function (Blueprint $table) {
		    $table->string('maturity_amount')->after('deposite_amount')->nullable();
		    $table->integer('interest_rate')->after('deposite_amount')->nullable();
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('member_loans', function (Blueprint $table) {
		    $table->dropColumn('maturity_amount');
		    $table->dropColumn('interest_rate');
	    });
    }
}
