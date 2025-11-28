<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillExpenseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('bill_expense', 'bill_expenses');

        Schema::create('bill_expense', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('daybook_refid');
            $table->Integer('bill_no');
            $table->Integer('payment_mode');
            $table->Integer('cheque_id');
            $table->Integer('utr_no');
            $table->Integer('neft_charge');            
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::rename('bill_expenses', 'bill_expense');
        Schema::dropIfExists('bill_expense');
    }
}
