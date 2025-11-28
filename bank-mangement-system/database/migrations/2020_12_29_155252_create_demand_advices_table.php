<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemandAdvicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demand_advices', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->integer('payment_type')->default(0)->comment('0=>expenses,1=>liability,2=>maturity,3=>prematurity');
            $table->bigInteger('branch_id')->unsigned()->nullable();
            $table->date('date')->nullable(true);
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
        Schema::dropIfExists('demand_advices');
    }
}
