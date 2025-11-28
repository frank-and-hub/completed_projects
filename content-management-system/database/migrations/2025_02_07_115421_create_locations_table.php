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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('city', 250);
            $table->string('state', 250);
            $table->string('country', 250);
            $table->string('title', 250)->nullable();
            $table->string('subtitle', 250)->nullable();
            $table->tinyInteger('status')->comment('0:Inactive,1:active')->default(0);
            $table->foreignId('thumbnail_id')->nullable()->constrained('media')->onDelete('set null');
            $table->foreignId('banner_id')->nullable()->constrained('media')->onDelete('set null');
            $table->softDeletes();
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
        Schema::dropIfExists('locations');
    }
};
