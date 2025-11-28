<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssociateDependentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('associate_dependents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->unsigned();
            $table->string('first_name'); 
            $table->string('last_name');
            $table->integer('age'); 
            $table->string('relation');    
            $table->tinyInteger('gender')->comment('1:male;0:female');   
            $table->tinyInteger('marital_status')->comment('1:married ;0:unmarried'); 
            $table->tinyInteger('living_with_associate')->comment('1:yes ;0:no'); 
            $table->decimal('monthly_income', 13, 4);
            $table->tinyInteger('dependent_type')->comment('1:fully ;0:partially'); 
            $table->tinyInteger('status')->default(1)->comment('1:active;0:inactive');
            $table->tinyInteger('is_deleted')->default(0)->comment('1:deleted;0:not deleted');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('associate_dependents');
    }
}
