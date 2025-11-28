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
        Schema::table('park_images', function (Blueprint $table) {
            $table->boolean('is_verified')->default(0)->after('set_as_banner');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('park_images', function (Blueprint $table) {
            $table->dropColumn('is_verified');
        });
    }
};
