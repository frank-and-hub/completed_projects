<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPartyNameToBillExpense extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_expenses', function (Blueprint $table) {
            $table->string('party_name');
            $table->string('party_bank_name');
            $table->string('party_bank_ac_no');
            $table->string('party_bank_ifsc');
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
            $table->dropColumn('party_name');
            $table->dropColumn('party_bank_name');
            $table->dropColumn('party_bank_ac_no');
            $table->dropColumn('party_bank_ifsc');
        });
    }
}
