<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->bigInteger('role_id')->unsigned()->after('first_name');            
            $table->bigInteger('special_category_id')->unsigned()->after('photo');
            $table->bigInteger('associate_id')->default(0)->after('role_id');
            $table->foreign('special_category_id')->references('id')->on('special_categories')->onUpdate('cascade');

            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade');
            
            
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
            $table->dropColumn(['special_category_id','role_id','associate_id']);
        });
    }
}
