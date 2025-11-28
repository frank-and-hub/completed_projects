<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeathHelpSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('death_help_settings', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            // $table->foreign('plan_id')->references('id')->on('plans');
            $table->integer('plan_code');
            $table->integer('plan_id');
            $table->integer('tenure');
            $table->integer('month_from');
            $table->integer('month_to');
            $table->decimal('death_help_per',10,4)->default('0');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->tinyInteger('status')->comment('1:active; 0:inactive')->default('1');
            $table->softDeletes();
            $table->tinyInteger('created_by')->comment('1:admin;')->default('1');
            $table->bigInteger('created_by_id');
            $table->date('created_at_default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('death_help_settings');
    }
}
