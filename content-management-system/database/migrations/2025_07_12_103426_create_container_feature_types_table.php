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
        Schema::create('container_feature_types', function (Blueprint $table) {
            $table->foreignId('container_id')->nullable()->constrained('containers')->onDelete('cascade');
            $table->foreignId('feature_type_id')->nullable()->constrained('feature_types')->onDelete('cascade');
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
        Schema::dropIfExists('container_feature_types');
    }
};
