<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSavingAccountTransactionReferenceToTransactionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_logs', function (Blueprint $table) {
            $table->bigInteger('saving_account_transaction_reference_id')->unsigned()->nullable(true)->after('id');
            $table->foreign('saving_account_transaction_reference_id')->references('id')->on('transaction_references')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_logs', function (Blueprint $table) {
            $table->dropColumn(['saving_account_transaction_reference_id']);
        });
    }
}
