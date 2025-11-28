<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->string('name')->nullable();
            $table->string('short_name',100)->nullable();
            $table->string('mobile_no',100)->nullable();
            $table->string('email',150)->nullable();
            $table->text('address')->nullable();
            $table->tinyInteger('fa_code_from',false, false)->nullable();
            $table->tinyInteger('fa_code_to',false, false)->nullable();
            $table->string('tin_no',100)->nullable();
            $table->string('pan_no',100)->nullable();
            $table->string('cin_no',100)->nullable();
            $table->string('uuid')->nullable();
            $table->tinyInteger('last_fa_code',false, false)->nullable();
            $table->tinyInteger('count',false, false)->nullable();
            $table->tinyInteger('status',false, false)->default(1)->comment('1:active;0:inactive');
            $table->enum('delete',['1','0'])->default('0')->comment('1:yes;0:no');
            $table->tinyInteger('created_by',false, false)->nullable()->comment('1:admin;0:default');
            $table->unsignedBigInteger('created_by_id')->nullable();
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
        Schema::dropIfExists('companies');
    }
}
