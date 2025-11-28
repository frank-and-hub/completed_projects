<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBankIdToBillExpense extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_expenses', function (Blueprint $table) {            
            $table->bigInteger('bank_id');
            $table->bigInteger('account_id');
            $table->bigInteger('bank_balance'); 
            $table->bigInteger('branch_balance')->nullable();        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bill_expenses', function (Blueprint $table) {
            $table->dropColumn('bank_id');
            $table->dropColumn('account_id');
            $table->dropColumn('bank_balance');
            $table->dropColumn('branch_balance');
        });
    }
}
