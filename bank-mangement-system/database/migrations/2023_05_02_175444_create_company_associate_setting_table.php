<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyAssociateSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_associate_setting', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('user_name',255);
            $table->bigInteger('company_id',false)->nullable();
            $table->bigInteger('user_id',false)->nullable();
            $table->enum('status',['0','1'])->comment('1:yes,0:no')->default('0');
            $table->enum('created_by',['0','1'])->comment('1:Admin,0:Branch')->default('1');
            $table->bigInteger('created_by_id',false);
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
        Schema::dropIfExists('company_associate_setting');
    }
}
