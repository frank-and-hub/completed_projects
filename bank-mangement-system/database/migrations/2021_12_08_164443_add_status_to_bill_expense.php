<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToBillExpense extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_expenses', function (Blueprint $table) {
            $table->bigInteger('status')->default(0)->comment('0:pending,1:approved,2:reject');
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
            $table->dropColumn('status');
        });
    }
}
