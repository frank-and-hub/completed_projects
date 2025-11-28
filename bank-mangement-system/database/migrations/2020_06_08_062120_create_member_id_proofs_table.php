<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberIdProofsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_id_proofs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->unsigned();
            $table->bigInteger('first_id_type_id')->unsigned(); 
            $table->string('first_id_no');
            $table->text('first_address'); 
            $table->bigInteger('second_id_type_id')->unsigned(); 
            $table->string('second_id_no');
            $table->text('second_address');        
            $table->tinyInteger('status')->default(1)->comment('1:active;0:inactive');
            $table->tinyInteger('is_deleted')->default(0)->comment('1:deleted;0:not deleted');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade');
            $table->foreign('first_id_type_id')->references('id')->on('id_types')->onUpdate('cascade');
            $table->foreign('second_id_type_id')->references('id')->on('id_types')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_id_proof');
    }
}
