<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemandAdvicesExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demand_advices_expenses', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->bigInteger('demand_advice_id')->unsigned()->nullable();
            $table->integer('payment_type')->default(0)->comment('0=>expenses,1=>liability,2=>maturity,3=>prematurity');
            $table->integer('expense_type')->default(0)->comment('0=>fresh expense/fe,1=>TA advance/ta,2=>imprest,3=>TA advance/imprest');
            $table->bigInteger('category')->unsigned()->nullable();
            $table->bigInteger('subcategory')->unsigned()->nullable();
            $table->string('party_name')->nullable(true);
            $table->string('particular')->nullable(true);
            $table->string('mobile_number')->nullable(true);
            $table->string('amount')->nullable(true);
            $table->string('bill_number')->nullable(true);
            $table->integer('bill_file_id')->nullable(true);
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->foreign('demand_advice_id')->references('id')->on('demand_advices')->onUpdate('cascade');
            $table->foreign('category')->references('id')->on('account_heads')->onUpdate('cascade');
            $table->foreign('subcategory')->references('id')->on('sub_account_heads')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demand_advices_expenses');
    }
}
