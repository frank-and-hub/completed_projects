<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSavingAccountTransactionReferenceToInvestmentPlanTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investment_plan_transactions', function (Blueprint $table) {
            $table->bigInteger('transaction_ref_id')->unsigned()->nullable(true)->after('id');
            $table->foreign('transaction_ref_id')->references('id')->on('transaction_references')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('investment_plan_transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_ref_id']);
        });
    }
}
