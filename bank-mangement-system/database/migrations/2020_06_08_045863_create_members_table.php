<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('form_no')->unique(); 
            $table->date('re_date');  
            $table->string('first_name'); 
            $table->string('last_name');             
            $table->string('mobile_no',100);
            $table->string('email'); 
            $table->date('dob');     
            $table->tinyInteger('gender')->default(1)->comment('1:Male;0:Female');
            $table->string('annual_income');  
            $table->bigInteger('occupation_id')->unsigned();
            $table->tinyInteger('marital_status')->default(1)->comment('1:married ;0:unmarried ');  
            $table->date('anniversary_date'); 
            $table->string('father_husband');
            $table->text('address');
            $table->bigInteger('state_id')->unsigned();
            $table->bigInteger('district_id')->unsigned();
            $table->bigInteger('city_id')->unsigned();
            $table->string('village');
            $table->string('pin_code');
            $table->bigInteger('religion_id')->unsigned();
            $table->string('mother_name');
            $table->string('signature');
            $table->string('photo');
            $table->tinyInteger('status')->default(1)->comment('1:active;0:inactive');
            $table->tinyInteger('is_deleted')->default(0)->comment('1:deleted;0:not deleted');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->foreign('occupation_id')->references('id')->on('occupations')->onUpdate('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onUpdate('cascade');
            $table->foreign('district_id')->references('id')->on('districts')->onUpdate('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onUpdate('cascade');
            $table->foreign('religion_id')->references('id')->on('religions')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
}
