<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCloumnToMemberInvestmentsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_investments_payments', function (Blueprint $table) {
            $table->bigInteger('ssb_account_id')->default(0); 
            $table->string('ssb_account_no',100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_investments_payments', function (Blueprint $table) {
            //
        });
    }
}
