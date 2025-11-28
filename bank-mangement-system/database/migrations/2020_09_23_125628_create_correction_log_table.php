<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorrectionLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_log', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();;
            $table->tinyInteger('correction_type')->comment('0:member;1:associate;2:investment;3:renewal;4:withdrawal;5:passbookprint;6:certificateprint')->nullable();
            $table->integer('correction_type_id')->nullable();
            $table->text('correction_log');
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('correction_log');
    }
}
