<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCloumnToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        
        Schema::table('members', function (Blueprint $table) {        

            $table->bigInteger('branch_id')->unsigned()->after('role_id'); 
            $table->integer('branch_code')->after('branch_id');
            $table->foreign('branch_id')->references('id')->on('branch')->onUpdate('cascade');
            

        });
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            //
        });
    }
}
