<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCloumnToMemberInvestmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_investments', function (Blueprint $table) {
            $table->string('investment_account_no',100)->nullable()->after('plan_id');
            $table->integer('mi_code')->default(0)->after('investment_account_no'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_investments', function (Blueprint $table) {
            //
        });
    }
}
