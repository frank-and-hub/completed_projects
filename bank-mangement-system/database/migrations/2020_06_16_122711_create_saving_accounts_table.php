<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSavingAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        
        Schema::create('saving_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('account_no',100)->unique();
            $table->bigInteger('member_id')->unsigned();
            $table->bigInteger('branch_id')->unsigned();
            $table->integer('branch_code');
            $table->decimal('balance', 13, 4);
            $table->string('currency_code',10);
            $table->bigInteger('created_by_id')->unsigned(); 
            $table->tinyInteger('created_by')->default(2)->comment('2:Branch;1:Admin;0:SuperAdmin');
            $table->tinyInteger('status')->default(1)->comment('1:active;0:inactive');
            $table->tinyInteger('is_deleted')->default(0)->comment('1:deleted;0:not deleted');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade');
            $table->foreign('branch_id')->references('id')->on('branch')->onUpdate('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->onUpdate('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saving_accounts');
    }
}
