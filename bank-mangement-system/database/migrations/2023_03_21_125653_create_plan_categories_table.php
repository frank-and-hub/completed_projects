<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_categories', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('name',100);
            $table->char('code',1);
            $table->tinyInteger('status')->default('1')->comment('1: active; 0: inactive');
            $table->tinyInteger('is_basic')->comment('1: yes; 0: no')->default('0');
            $table->bigInteger('head_id')->nullable();
            $table->tinyInteger('created_by')->comment('1: admin;')->default('1');
            $table->bigInteger('created_by_id');
            $table->timestamp('created_at_default')->useCurrent();
            $table->string('slug');
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
        Schema::dropIfExists('plan_categories');
    }
}
