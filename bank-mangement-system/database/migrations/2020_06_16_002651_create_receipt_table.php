<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	Schema::dropIfExists('receipts');
        Schema::create('receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('transaction_id')->unsigned();
            $table->bigInteger('member_id')->unsigned();
            $table->bigInteger('receipt_by_id')->unsigned();
            $table->string('account_no',100);
            $table->bigInteger('branch_id')->unsigned();  
            $table->integer('branch_code'); 
            $table->bigInteger('created_by_id')->unsigned(); 
            $table->tinyInteger('created_by')->default(2)->comment('2:Branch;1:Admin;0:SuperAdmin');       
            $table->tinyInteger('status')->default(1)->comment('1:active;0:inactive');
            $table->tinyInteger('is_deleted')->default(0)->comment('1:deleted;0:not deleted');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade');
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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('receipts');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
