<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyBranchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_branch', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->unsignedBigInteger('company_id')->nullable();
			$table->foreign('company_id')->references('id')->on('companies');
			$table->unsignedBigInteger('branch_id')->nullable();
			$table->foreign('branch_id')->references('id')->on('branch');
			$table->enum('created_by',['1','0'])->default('1')->comment('1=>admin,0:branch');
			$table->unsignedBigInteger('created_by_id')->nullable();
			$table->enum('status',['1','0'])->default('1')->comment('1=>active,0:inactive');
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
        Schema::dropIfExists('company_branch');
    }
}
