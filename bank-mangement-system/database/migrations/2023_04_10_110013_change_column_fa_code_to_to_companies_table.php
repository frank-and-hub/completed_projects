<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnFaCodeToToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('fa_code_from');
            $table->dropColumn('fa_code_to');
            $table->dropColumn('last_fa_code');
            $table->dropColumn('count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->bigInteger('fa_code_from',false)->nullable();
            $table->bigInteger('fa_code_to',false)->nullable();
            $table->bigInteger('last_fa_code',false)->nullable();
            $table->bigInteger('count',false)->nullable();
        });
    }
}
