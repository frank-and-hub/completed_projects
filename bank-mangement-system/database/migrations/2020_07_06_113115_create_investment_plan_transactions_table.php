<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestmentPlanTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('investment_plan_transactions', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->bigInteger('investment_id')->unsigned()->nullable(true);
            $table->integer('plan_id')->nullable(true);
            $table->bigInteger('member_id')->unsigned(true);
            $table->bigInteger('branch_id')->unsigned(true)->nullable(true);
            $table->integer('branch_code')->nullable(true);
            $table->string('deposite_amount', 255)->nullable(true);
            $table->date('deposite_date')->nullable(true);
            $table->integer('payment_mode')->default(0)->comment('0=>cash,1=>cheque,2=>online transaction,3=>ssb account');
            $table->bigInteger('saving_account_id')->unsigned(true)->nullable(true);
            $table->integer('status')->default(0)->comment('1=>active,0=>inactive');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('investment_id')->references('id')->on('member_investments')->onUpdate('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onUpdate('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade');
            $table->foreign('branch_id')->references('id')->on('branch')->onUpdate('cascade');
            $table->foreign('saving_account_id')->references('id')->on('saving_accounts')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('investment_plan_transactions');
    }
}
