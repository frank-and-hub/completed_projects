<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanDenosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_denos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->bigInteger('plan_id')->unsigned();
            $table->integer('plan_code',false);
            $table->integer('tenure',false);
            $table->decimal('denomination',30,4)->default('0');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->tinyInteger('status')->comment('1:active;0:inactive')->default('1');
            $table->softDeletes();
            $table->tinyInteger('created_by')->comment('1:admin;')->default('1');
            $table->bigInteger('created_by_id');
            $table->timestamp('created_at_default')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_denos');
    }
}
