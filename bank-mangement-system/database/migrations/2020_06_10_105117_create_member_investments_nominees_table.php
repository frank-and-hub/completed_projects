<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberInvestmentsNomineesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_investments_nominees', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->bigInteger('investment_id')->unsigned()->nullable(true);
            $table->integer('nominee_type')->default(0)->comment('0=>first,1=>second');
            $table->string('first_name', 100)->nullable(true);
            $table->string('second_name', 100)->nullable(true);
            $table->string('relation', 50)->nullable(true);
            $table->integer('gender')->default(0)->comment('0=>male,1=>female');
            $table->dateTime('dob', 0)->nullable(true);
            $table->integer('age')->nullable(true);
            $table->integer('percentage')->nullable(true);
            $table->string('phone_number', 100)->nullable(true);
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('investment_id')->references('id')->on('member_investments')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_investments_nominees');
    }
}
