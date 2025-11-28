<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_loans', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->integer('loan_type')->default(0)->comment('0=>personal loan,1=>group loan,2=>staff loan');
            $table->bigInteger('associate_member_id')->unsigned()->nullable(true);
            $table->integer('applicant_id')->nullable(true);
            $table->string('group_activity', 255)->nullable(true);
            $table->bigInteger('groupleader_member_id')->unsigned()->nullable(true);
            $table->bigInteger('group_member_id')->unsigned()->nullable(true);
            $table->string('amount', 255)->nullable(true);
            $table->integer('emi_mode_in_day')->nullable(true);
            $table->integer('emi_mode_in_month')->nullable(true);
            $table->integer('emi_mode_in_week')->nullable(true);
            $table->integer('number_of_member')->nullable(true);
            $table->string('loan_purpose', 255)->nullable(true);
            $table->string('bank_account', 255)->nullable(true);
            $table->string('ifsc_code', 255)->nullable(true);
            $table->string('bank_name', 255)->nullable(true);
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('associate_member_id')->references('id')->on('members')->onUpdate('cascade');
            $table->foreign('groupleader_member_id')->references('id')->on('members')->onUpdate('cascade');
            $table->foreign('group_member_id')->references('id')->on('members')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_loans');
    }
}
