<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberInvestmentsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_investments_payments', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->bigInteger('investment_id')->unsigned()->nullable(true);
            $table->integer('cheque_number')->nullable(false);
            $table->string('bank_name')->nullable(false);
            $table->string('branch_name')->nullable(false);
            $table->date('cheque_date')->nullable(false);
            $table->integer('transaction_id')->nullable(false);
            $table->date('transaction_date')->nullable(false);
            $table->string('ssb_amount')->nullable(false);
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('investment_id')->references('id')->on('member_investments')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_investments_payments');
    }
}
