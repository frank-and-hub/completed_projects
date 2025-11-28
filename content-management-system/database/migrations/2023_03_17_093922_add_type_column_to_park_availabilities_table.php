<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('park_availabilities', function (Blueprint $table) {
            $table->enum('type',['dawn_to_dusk','24_hours','custom']);
        });
    }


    public function down()
    {
        Schema::table('park_availabilities', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
