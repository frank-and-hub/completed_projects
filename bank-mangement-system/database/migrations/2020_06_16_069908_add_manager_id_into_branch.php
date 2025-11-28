<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMangerIdIntoUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('branch', function (Blueprint $table) {
		    $table->unsignedBigInteger('manager_id')->after('branch_code');
		    $table->foreign('manager_id')->references('id')->on('users');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('branch', function (Blueprint $table) {
		    $table->dropColumn('manager_id');
	    });
    }
}
