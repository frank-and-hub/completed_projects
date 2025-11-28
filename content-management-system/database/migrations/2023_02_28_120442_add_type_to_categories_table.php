<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('priority')->nullable()->after('name');
            $table->enum('type',['no-child','parent','special'])->default('parent')->after('priority');
            $table->boolean('is_set_as_home')->default(0)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('priority');
            $table->dropColumn('type');
            $table->dropColumn('is_set_as_home');
        });
    }
};
