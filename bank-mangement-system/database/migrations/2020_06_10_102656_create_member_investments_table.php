<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberInvestmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_investments', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->foreign('plan_id')->references('id')->on('plans');
            $table->integer('plan_id')->nullable(true);
            $table->integer('form_number')->nullable(true);
            $table->bigInteger('member_id')->unsigned();
            $table->integer('branch_id')->nullable(true);
            $table->string('deposite_amount', 255)->nullable(true);
            $table->string('guardians_relation', 50)->nullable(true);
            $table->string('daughter_name', 50)->nullable(true);
            $table->string('phone_number', 100)->nullable(true);
            $table->dateTime('dob', 0)->nullable(true);
            $table->string('age', 11)->nullable(true);
            $table->integer('duration')->nullable(true);
            $table->string('tenure', 255)->nullable(true);
            $table->integer('payment_mode')->default(0)->comment('0=>cash,1=>cheque,2=>online transaction,3=>ssb account');
            $table->integer('category')->default(0)->comment('0=>special,1=>general');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('member_id')->references('member_id')->on('members')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_investments');
    }
}
