<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDayBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('day_books', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('transaction_id')->unsigned();
            $table->bigInteger('investment_id')->nullable();
            $table->string('account_no',100);
            $table->bigInteger('associate_id')->unsigned();
            $table->bigInteger('branch_id')->unsigned();
            $table->integer('branch_code');
            $table->decimal('amount', 13, 4);
            $table->string('currency_code',10);
            $table->tinyInteger('payment_mode')->default(0)->comment('0:cash;1:cheque;2:dd;3:online_transaction;4:by saving account');
            $table->bigInteger('saving_account_id')->default(0);
            $table->integer('cheque_dd_no')->default(0);
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('online_payment_id')->nullable();
            $table->string('online_payment_by')->nullable();
            $table->string('amount_deposit_by_name')->nullable();
            $table->bigInteger('amount_deposit_by_id')->default(0);
            $table->bigInteger('created_by_id')->unsigned(); 
            $table->tinyInteger('created_by')->default(2)->comment('2:Branch;1:Admin;0:SuperAdmin');
             $table->tinyInteger('status')->default(1)->comment('1:active;0:inactive');
            $table->tinyInteger('is_deleted')->default(0)->comment('1:deleted;0:not deleted');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('associate_id')->references('id')->on('members')->onUpdate('cascade');
            $table->foreign('branch_id')->references('id')->on('branch')->onUpdate('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('day_books');
    }
}
