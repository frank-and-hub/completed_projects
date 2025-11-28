<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssociateGuarantorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('associate_guarantors', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->bigInteger('member_id')->unsigned();
            $table->string('first_first_name'); 
            $table->string('first_last_name');
            $table->string('first_mobile_no',100); 
            $table->text('first_address'); 
            $table->string('second_first_name'); 
            $table->string('second_last_name');
            $table->string('second_mobile_no',100); 
            $table->text('second_address');    

            $table->tinyInteger('status')->default(1)->comment('1:active;0:inactive');
            $table->tinyInteger('is_deleted')->default(0)->comment('1:deleted;0:not deleted');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('associate_guarantors');
    }
}
