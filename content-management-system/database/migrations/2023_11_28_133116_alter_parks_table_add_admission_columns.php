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
        Schema::table('parks', function (Blueprint $table) {
            $table->boolean('is_paid')->default(0)->after('active');
            $table->longText('instructions')->nullable()->after('is_paid');
            $table->string('instruction_url')->nullable()->after('instructions');
            $table->decimal('ticket_amount',8)->nullable()->after('instruction_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parks', function (Blueprint $table) {
            $table->dropColumn(['is_paid','instructions','instruction_url','ticket_amount']);
        });
    }
};
