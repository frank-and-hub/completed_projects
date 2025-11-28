<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansGuarantorDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans_guarantor_details', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->bigInteger('member_loan_id')->unsigned()->nullable(true);
            $table->bigInteger('member_id')->unsigned()->nullable(true);
            $table->string('name', 100)->nullable(true);
            $table->string('father_name', 100)->nullable(true);
            $table->date('dob')->nullable(true);
            $table->integer('marital_status')->default(0)->comment('0=>single,1=>married');
            $table->string('local_address', 100)->nullable(true);
            $table->integer('ownership')->default(0)->comment('0=>self,1=>perental,2=>Rental');
            $table->integer('temporary_permanent')->default(0)->comment('0=>self,1=>perental,2=>Rental');
            $table->string('mobile_number', 100)->nullable(true);
            $table->integer('educational_qualification')->default(0)->comment('0=>higher secondary,1=>junior high school,2=>graduation,3=>post graduation');
            $table->integer('number_of_dependents')->nullable(true);
            $table->string('occupation', 100)->nullable(true);
            $table->string('organization', 255)->nullable(true);
            $table->string('designation', 100)->nullable(true);
            $table->string('monthly_income', 100)->nullable(true);
            $table->string('year_from', 100)->nullable(true);
            $table->string('bank_name', 100)->nullable(true);
            $table->string('bank_account_number', 100)->nullable(true);
            $table->string('ifsc_code', 100)->nullable(true);
            $table->string('cheque_number_1', 100)->nullable(true);
            $table->string('cheque_number_2', 100)->nullable(true);
            $table->integer('id_proof_type')->default(0)->comment('0=>pen card,1=>aadhar card,2=>dl,3=>voter id,4=>passport,5=>identity card');
            $table->string('id_proof_number', 100)->nullable(true);
            $table->bigInteger('id_proof_file_id')->unsigned()->nullable(true);
            $table->integer('address_proof_type')->default(0)->comment('0=>aadhar card,1=>dl,2=>voter id,3=>passport,4=>identity card,5=>bank passbook,6=>electricity bill,7=>telephone bill');
            $table->string('address_proof_id_number', 100)->nullable(true);
            $table->bigInteger('address_proof_file_id')->unsigned()->nullable(true);
            $table->bigInteger('under_taking_doc')->unsigned()->nullable(true);
            $table->integer('income_type')->default(0)->comment('0=>salary slip ,1=>ITR,2=>others');
            $table->string('income_remark', 100)->nullable(true);
            $table->bigInteger('income_file_id')->unsigned()->nullable(true);
            $table->integer('security')->default(0)->comment('0=>cheuqe,1=>passbook,2=>FD certificate');
            $table->string('more_doc_title', 100)->nullable(true);
            $table->bigInteger('more_doc_file_id')->unsigned()->nullable(true);
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade');
            $table->foreign('member_loan_id')->references('id')->on('member_loans')->onUpdate('cascade');
            $table->foreign('id_proof_file_id')->references('id')->on('files')->onUpdate('cascade');
            $table->foreign('address_proof_file_id')->references('id')->on('files')->onUpdate('cascade');
            $table->foreign('under_taking_doc')->references('id')->on('files')->onUpdate('cascade');
            $table->foreign('income_file_id')->references('id')->on('files')->onUpdate('cascade');
            $table->foreign('more_doc_file_id')->references('id')->on('files')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans_guarantor_details');
    }
}
