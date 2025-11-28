<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToRentLedgers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rent_ledgers', function (Blueprint $table) {
            $table->decimal('tds_amount', 20, 4);
            $table->decimal('payable_amount', 20, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rent_ledgers', function (Blueprint $table) {
            //
        });
    }
}
