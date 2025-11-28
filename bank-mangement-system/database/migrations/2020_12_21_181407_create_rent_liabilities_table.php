<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentLiabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rent_liabilities', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->bigInteger('branch_id')->unsigned()->nullable();
            $table->integer('rent_type')->nullable();
            $table->date('date',100)->nullable();
            $table->date('agreement_from',100)->nullable();
            $table->date('agreement_to',100)->nullable();
            $table->string('place',255)->nullable();
            $table->string('owner_name',255)->nullable();
            $table->string('owner_mobile_number',255)->nullable();
            $table->string('owner_pen_number',255)->nullable();
            $table->string('owner_aadhar_number',255)->nullable();
            $table->string('owner_ssb_number',255)->nullable();
            $table->string('owner_bank_name',255)->nullable();
            $table->string('owner_bank_account_number',255)->nullable();
            $table->string('owner_bank_ifsc_code',255)->nullable();
            $table->string('security_amount',255)->nullable();
            $table->string('rent',255)->nullable();
            $table->string('yearly_increment',255)->nullable();
            $table->string('office_area',255)->nullable();
            $table->string('employee_code',255)->nullable();
            $table->string('authorized_employee_name',255)->nullable();
            $table->string('authorized_employee_designation',255)->nullable();
            $table->string('mobile_number',255)->nullable();
            $table->string('rent_agreement_file_id',255)->nullable();  
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('rent_liabilities');
    }
}
