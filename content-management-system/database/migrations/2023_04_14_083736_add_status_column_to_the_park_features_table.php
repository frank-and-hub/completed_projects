<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('park_features', function (Blueprint $table) {
            $table->boolean('active')->default(1)->nullable()->after('feature_id');

        });
    }

    public function down()
    {
        Schema::table('park_features', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
