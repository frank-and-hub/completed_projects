<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToInvestmentMonthlyYearlyInterestDeposits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investment_monthly_yearly_interest_deposits', function (Blueprint $table) {
            $table->decimal('carry_forward_amount',30,2);
            $table->decimal('fd_amount_with_interest',30,2);
            $table->decimal('interest_amount',30,2);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('investment_monthly_yearly_interest_deposits', function (Blueprint $table) {
            //
        });
    }
}
