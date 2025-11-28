<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToDayBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('day_books', function (Blueprint $table) {
            $table->decimal('opening_balance', 10, 2)->after('member_id')->nullable(true);
            $table->decimal('deposit', 10, 2)->after('opening_balance')->nullable(true);
            $table->decimal('withdrawal', 10, 2)->after('deposit')->nullable(true);
            $table->text('description')->after('withdrawal')->nullable(true);
            $table->string('reference_no',255)->after('description')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('day_books', function (Blueprint $table) {
            $table->dropColumn(['opening_balance']);
            $table->dropColumn(['deposit']);
            $table->dropColumn(['withdrawal']);
            $table->dropColumn(['description']);
            $table->dropColumn(['reference_no']);
        });
    }
}
