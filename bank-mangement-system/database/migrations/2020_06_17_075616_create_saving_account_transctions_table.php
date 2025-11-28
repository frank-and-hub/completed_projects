<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSavingAccountTransctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saving_account_transctions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('saving_account_id')->unsigned();
            $table->string('account_no',100);
            $table->decimal('amount', 13, 4);
            $table->string('currency_code',10);
            $table->enum('payment_type',['DR','CR'])->comment('DR:debit;CR:credit');
            $table->tinyInteger('payment_mode')->default(0)->comment('0:cash ;1:cheque;2:dd;3:transfer;4:atm;5:online');
            $table->tinyInteger('status')->default(1)->comment('1:active;0:inactive'); 
            $table->tinyInteger('is_deleted')->default(0)->comment('1:deleted;0:not deleted');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('saving_account_transctions');
    }
}
