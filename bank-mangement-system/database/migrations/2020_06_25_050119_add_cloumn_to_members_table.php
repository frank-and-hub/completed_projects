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
            $table->tinyInteger('is_associate')->default(0)->comment('1:yes;0:no');
            $table->integer('associate_micode')->default(0);
            $table->integer('associate_facode')->default(0);
            $table->string('associate_no',100)->nullable();
            $table->string('associate_form_no')->nullable(); 
            $table->date('associate_join_date')->nullable(); 
            $table->string('associate_senior_code',100)->nullable();
            $table->bigInteger('associate_senior_id')->default(0);
            $table->bigInteger('current_carder_id')->default(0);
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
