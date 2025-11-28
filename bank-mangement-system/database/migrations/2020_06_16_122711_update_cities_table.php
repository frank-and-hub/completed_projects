<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        
        if (!Schema::hasColumn('cities', 'district_id'))
        {

            Schema::table('cities', function (Blueprint $table)
            {
                $table->unsignedBigInteger('district_id')->after('id');
                $table->foreign('district_id')->references('id')->on('districts');

            });
                
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            //
        });
    }
}
