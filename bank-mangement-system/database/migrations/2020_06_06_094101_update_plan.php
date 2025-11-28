<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('plan', 'created_at'))
        {
            Schema::table('plan', function (Blueprint $table)
            {
                $table->dropColumn('created_at');

            });
        }
        if (Schema::hasColumn('plan', 'updated_at'))
        {
            Schema::table('plan', function (Blueprint $table)
            {
                $table->dropColumn('updated_at');

            });
        }
        Schema::table('plan', function (Blueprint $table) { 
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
        if (Schema::hasColumn('plan', 'created_at'))
        {
            Schema::table('plan', function (Blueprint $table)
            {
                $table->dropColumn('created_at');

            });
        }
        if (Schema::hasColumn('plan', 'updated_at'))
        {
            Schema::table('plan', function (Blueprint $table)
            {
                $table->dropColumn('updated_at');

            });
        }
    }
}
