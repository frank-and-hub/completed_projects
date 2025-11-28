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
        Schema::create('container_park', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->nullable()->constrained('containers')->onDelete('cascade');
            $table->foreignId('park_id')->nullable()->constrained('parks')->onDelete('cascade');
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
        Schema::dropIfExists('container_park');
    }
};
