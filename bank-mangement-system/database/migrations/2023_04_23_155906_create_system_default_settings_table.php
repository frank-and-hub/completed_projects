<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemDefaultSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_default_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('name')->nullable();
			$table->string('short_name')->nullable();
			$table->decimal('amount', 15, 4);
			$table->date('effective_from')->nullable();
			$table->date('effective_to')->nullable();
			$table->bigInteger('head_id')->unsigned()->nullable();
			$table->bigInteger('company_id')->unsigned()->nullable();
			$table->tinyInteger('status')->comment('1:active;0:inactive')->nullable();
			$table->tinyInteger('delete')->comment('1:yes;0:no');
			$table->tinyInteger('created_by')->comment('1:admin;0:default');
			$table->bigInteger('created_by_id')->nullable();
			$table->timestamp('created_at_default')->default(DB::raw('CURRENT_TIMESTAMP'));		
			$table->foreign('head_id')->references('head_id')->on('account_heads');
			$table->foreign('company_id')->references('id')->on('company_table');
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
        Schema::dropIfExists('system_default_settings');
    }
}
