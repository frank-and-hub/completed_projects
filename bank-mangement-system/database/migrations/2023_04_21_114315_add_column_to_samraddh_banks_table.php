<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToSamraddhBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('samraddh_banks', function (Blueprint $table) {
            $table->string('company_id');
            $table->tinyInteger('created_by')->comment('1:admin')->default('1');
            $table->dropColumn('sub_account_head_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('samraddh_banks', function (Blueprint $table) {
            //
        });
    }
}
